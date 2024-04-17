<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\Internal;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Typhoon\TypeContext\FullyQualifiedName;
use Typhoon\TypeContext\Name;
use Typhoon\TypeContext\QualifiedName;
use Typhoon\TypeContext\RelativeName;
use Typhoon\TypeContext\TypeContext;
use Typhoon\TypeContext\UnqualifiedName;

/**
 * @internal
 * @psalm-internal Typhoon\TypeContext
 * @psalm-suppress UnusedClass
 * @todo Move to Reflection
 */
final class TypeContextVisitor extends NodeVisitorAbstract
{
    private TypeContext $mainContext;

    /**
     * @var list<TypeContext>
     */
    private array $classContexts = [];

    public function __construct(
        TypeContext $contextPrototype = new TypeContext(),
    ) {
        $this->mainContext = $contextPrototype->atNamespace();
    }

    public function context(): TypeContext
    {
        return reset($this->classContexts) ?: $this->mainContext;
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
                $this->addUse(type: $node->type, nameNode: $use->name, aliasNode: $use->alias);
            }

            return null;
        }

        if ($node instanceof GroupUse) {
            foreach ($node->uses as $use) {
                $this->addUse(
                    type: $node->type | $use->type,
                    nameNode: NameNode::concat($node->prefix, $use->name),
                    aliasNode: $use->alias,
                );
            }

            return null;
        }

        if ($node instanceof ClassLike) {
            $parentName = null;

            if ($node instanceof Class_ && $node->extends !== null) {
                $parentName = $this->mainContext->resolveClassName($this->nameFromNode($node->extends));
            }

            if ($node->name === null) {
                $this->classContexts[] = $this->context()->atAnonymousClass($parentName);

                return null;
            }

            $name = $this->mainContext->resolveDeclaredName(UnqualifiedName::fromString($node->name->name));

            if ($node instanceof Trait_) {
                $this->classContexts[] = $this->context()->atTrait($name);

                return null;
            }

            $this->classContexts[] = $this->context()->atClass($name, $parentName);

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

        if ($node instanceof ClassLike) {
            array_pop($this->classContexts);

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
        $this->mainContext = $this->mainContext->atNamespace($namespace === null ? null : $this->nameFromNode($namespace));
        $this->classContexts = [];
    }

    private function addUse(int $type, NameNode $nameNode, ?Identifier $aliasNode): void
    {
        $name = $this->nameFromNode($nameNode);
        $alias = $aliasNode === null ? null : UnqualifiedName::fromString($aliasNode->name);

        $this->mainContext = match ($type) {
            Use_::TYPE_NORMAL => $this->mainContext->withUse($name, $alias),
            Use_::TYPE_FUNCTION => $this->mainContext->withFunctionUse($name, $alias),
            Use_::TYPE_CONSTANT => $this->mainContext->withConstantUse($name, $alias),
        };
    }

    private function nameFromNode(NameNode $name): Name
    {
        $parts = $name->getParts();
        \assert($parts !== [] && array_is_list($parts));
        $segments = array_map(UnqualifiedName::fromString(...), $parts);

        if ($name instanceof FullyQualified) {
            return new FullyQualifiedName($segments);
        }

        if ($name instanceof Relative) {
            return new RelativeName($segments);
        }

        if (\count($segments) === 1) {
            return $segments[0];
        }

        return new QualifiedName($segments);
    }
}
