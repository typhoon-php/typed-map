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
use Typhoon\TypeContext\TypeContext;

/**
 * @api
 */
final class TypeContextVisitor implements NodeVisitor, TypeContextProvider
{
    private TypeContext $mainContext;

    /**
     * @var list<TypeContext>
     */
    private array $symbolContexts = [];

    public function __construct(
        TypeContext $contextPrototype = new TypeContext(),
        private readonly TypeContextProcessor $processor = new NullTypeContextProcessor(),
    ) {
        $this->mainContext = $contextPrototype->atNamespace();
    }

    public function typeContext(): TypeContext
    {
        return reset($this->symbolContexts) ?: $this->mainContext;
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
            $this->symbolContexts[] = $this->processor->process($this->createClassContext($node), $node);

            return null;
        }

        if ($node instanceof FunctionLike) {
            $this->symbolContexts[] = $this->processor->process($this->typeContext(), $node);

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
            \assert(array_pop($this->symbolContexts) !== null);

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
        $this->mainContext = $this->mainContext->atNamespace($namespace);
        $this->symbolContexts = [];
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
            return $this->typeContext()->atAnonymousClass($parentName);
        }

        if ($node instanceof Trait_) {
            return $this->typeContext()->atTrait($name);
        }

        return $this->typeContext()->atClass($name, $parentName);
    }
}
