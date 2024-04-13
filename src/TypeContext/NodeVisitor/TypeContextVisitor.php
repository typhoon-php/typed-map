<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor;
use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\TypeContext\TypeContext;
use function Typhoon\DeclarationId\anonymousClassId;

/**
 * @api
 */
final class TypeContextVisitor implements NodeVisitor, TypeContextProvider
{
    private TypeContext $mainContext;

    /**
     * @var list<TypeContext>
     */
    private array $childContextStack = [];

    /**
     * @param ?non-empty-string $file
     */
    public function __construct(
        private readonly ?string $file = null,
    ) {
        $this->mainContext = new TypeContext();
    }

    public function typeContext(): TypeContext
    {
        return reset($this->childContextStack) ?: $this->mainContext;
    }

    public function beforeTraverse(array $nodes): ?int
    {
        $this->enterNamespace();

        return null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Namespace_) {
            $this->enterNamespace($node->name);

            return null;
        }

        if ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $this->addUse($node->type, $use->name, $use->alias);
            }

            return null;
        }

        if ($node instanceof GroupUse) {
            foreach ($node->uses as $use) {
                $this->addUse($node->type | $use->type, NameNode::concat($node->prefix, $use->name), $use->alias);
            }

            return null;
        }

        if ($node instanceof ClassLike) {
            $this->childContextStack[] = $this->createClassContext($node);

            return null;
        }

        if ($node instanceof FunctionLike) {
            $this->childContextStack[] = $this->typeContext();

            return null;
        }

        return null;
    }

    public function leaveNode(Node $node): ?int
    {
        if ($node instanceof Namespace_) {
            $this->enterNamespace();

            return null;
        }

        if ($node instanceof ClassLike || $node instanceof FunctionLike) {
            \assert(array_pop($this->childContextStack) !== null);

            return null;
        }

        return null;
    }

    public function afterTraverse(array $nodes): ?int
    {
        $this->enterNamespace();

        return null;
    }

    private function enterNamespace(?NameNode $namespace = null): void
    {
        $this->mainContext = new TypeContext($namespace);
        $this->childContextStack = [];
    }

    private function addUse(int $type, NameNode $name, ?Identifier $alias): void
    {
        $this->mainContext = match ($type) {
            Use_::TYPE_NORMAL => $this->mainContext->withUse($name, $alias),
            Use_::TYPE_FUNCTION => $this->mainContext->withFunctionUse($name, $alias),
            Use_::TYPE_CONSTANT => $this->mainContext->withConstantUse($name, $alias),
        };
    }

    private function createClassContext(ClassLike $node): TypeContext
    {
        $name = $node->name;
        $parentName = $node instanceof Class_ ? $node->extends : null;

        if ($name === null) {
            return $this->typeContext()->atAnonymousClass($this->anonymousClassId($node), $parentName);
        }

        if ($node instanceof Trait_) {
            return $this->typeContext()->atTrait($name);
        }

        return $this->typeContext()->atClass($name, $parentName);
    }

    private function anonymousClassId(ClassLike $node): AnonymousClassId
    {
        if ($this->file === null) {
            throw new \LogicException();
        }

        $startLine = $node->getStartLine();

        if ($startLine <= 0) {
            throw new \LogicException();
        }

        return anonymousClassId($this->file, $startLine);
    }
}
