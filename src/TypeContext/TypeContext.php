<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name as NameNode;
use Typhoon\Type\At;
use Typhoon\Type\AtClass;
use Typhoon\Type\AtFunction;
use Typhoon\Type\AtMethod;
use Typhoon\Type\Type;
use Typhoon\Type\types;
use Typhoon\TypeContext\Internal\ConstantImportTable;
use Typhoon\TypeContext\Internal\FunctionImportTable;
use Typhoon\TypeContext\Internal\MainImportTable;

/**
 * @api
 * @readonly
 * @psalm-type Exists = callable(non-empty-string): bool
 */
final class TypeContext
{
    /**
     * @readonly
     */
    public ?FullyQualifiedName $namespace;

    /**
     * @var Exists
     */
    private readonly mixed $classExists;

    /**
     * @var Exists
     */
    private readonly mixed $functionExists;

    /**
     * @var Exists
     */
    private readonly mixed $constantExists;

    private MainImportTable $mainImportTable;

    private FunctionImportTable $functionImportTable;

    private ConstantImportTable $constantImportTable;

    /**
     * @param ?Exists $classExists
     * @param ?Exists $functionExists
     * @param ?Exists $constantExists
     */
    public function __construct(
        ?Name $namespace = null,
        ?callable $classExists = null,
        ?callable $functionExists = null,
        ?callable $constantExists = null,
    ) {
        $this->classExists = $classExists ?? static fn(string $class): bool => class_exists($class) || interface_exists($class);
        $this->functionExists = $functionExists ?? 'function_exists';
        $this->constantExists = $constantExists ?? 'defined';
        $this->namespace = $namespace?->toFullyQualified();
        $this->mainImportTable = new MainImportTable();
        $this->functionImportTable = new FunctionImportTable();
        $this->constantImportTable = new ConstantImportTable();
    }

    public function atNamespace(null|Name|NameNode $namespace = null): self
    {
        if ($namespace instanceof NameNode) {
            $namespace = Name::fromNode($namespace);
        }

        $context = clone $this;
        $context->namespace = $namespace?->toFullyQualified();
        $context->mainImportTable = new MainImportTable();
        $context->functionImportTable = new FunctionImportTable();
        $context->constantImportTable = new ConstantImportTable();

        return $context;
    }

    public function withUse(Name|NameNode $name, null|UnqualifiedName|Identifier $alias = null): self
    {
        if ($name instanceof NameNode) {
            $name = Name::fromNode($name);
        }

        if ($alias instanceof Identifier) {
            $alias = UnqualifiedName::fromIdentifier($alias);
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->withName($name, $alias);

        return $context;
    }

    public function withFunctionUse(Name|NameNode $name, null|UnqualifiedName|Identifier $alias = null): self
    {
        if ($name instanceof NameNode) {
            $name = Name::fromNode($name);
        }

        if ($alias instanceof Identifier) {
            $alias = UnqualifiedName::fromIdentifier($alias);
        }

        $context = clone $this;
        $context->functionImportTable = $context->functionImportTable->withName($name, $alias);

        return $context;
    }

    public function withConstantUse(Name|NameNode $name, null|UnqualifiedName|Identifier $alias = null): self
    {
        if ($name instanceof NameNode) {
            $name = Name::fromNode($name);
        }

        if ($alias instanceof Identifier) {
            $alias = UnqualifiedName::fromIdentifier($alias);
        }

        $context = clone $this;
        $context->constantImportTable = $context->constantImportTable->withName($name, $alias);

        return $context;
    }

    public function atClass(FullyQualifiedName|Identifier $name, null|Name|NameNode $parentName = null): self
    {
        if ($name instanceof Identifier) {
            $name = $this->resolveDeclaredName($name);
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->atClass($name, $this->resolveClassName($parentName));

        return $context;
    }

    public function atAnonymousClass(null|Name|NameNode $parentName = null): self
    {
        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->atAnonymousClass($this->resolveClassName($parentName));

        return $context;
    }

    public function atTrait(FullyQualifiedName|Identifier $name): self
    {
        if ($name instanceof Identifier) {
            $name = $this->resolveDeclaredName($name);
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->atTrait($name);

        return $context;
    }

    /**
     * @param array<UnqualifiedName> $names
     * @param non-empty-string $class
     */
    public function withAliases(array $names, string $class): self
    {
        if ($names === []) {
            return $this;
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->withAliases($names, $class);

        return $context;
    }

    /**
     * @param array<UnqualifiedName> $names
     */
    public function withTemplates(array $names, At|AtFunction|AtClass|AtMethod $declaredAt): self
    {
        if ($names === []) {
            return $this;
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->withTemplates($names, $declaredAt);

        return $context;
    }

    public function resolveDeclaredName(UnqualifiedName|Identifier $name): FullyQualifiedName
    {
        if ($name instanceof Identifier) {
            $name = UnqualifiedName::fromIdentifier($name);
        }

        return new FullyQualifiedName([...($this->namespace?->segments ?? []), $name]);
    }

    /**
     * @return ($name is null ? null : FullyQualifiedName)
     */
    public function resolveClassName(null|Name|NameNode $name): ?FullyQualifiedName
    {
        if ($name === null) {
            return null;
        }

        return $this->doResolveName($name, function (UnqualifiedName $name): FullyQualifiedName {
            $imported = $this->mainImportTable->getName($name);

            if ($imported !== null) {
                return $imported;
            }

            if ($name->isClassRelativeName()) {
                throw new InvalidName(sprintf('Cannot resolve %s', $name->toString()));
            }

            return new FullyQualifiedName([...($this->namespace?->segments ?? []), $name]);
        });
    }

    public function resolveFunctionName(Name|NameNode $name): FullyQualifiedName
    {
        return $this->doResolveName($name, function (UnqualifiedName $name): FullyQualifiedName {
            $imported = $this->functionImportTable->getName($name);

            if ($imported !== null) {
                return $imported;
            }

            if ($this->namespace === null) {
                return new FullyQualifiedName([$name]);
            }

            $namespacedName = new FullyQualifiedName([...$this->namespace->segments, $name]);

            if (($this->functionExists)($namespacedName->toStringWithoutSlash())) {
                return $namespacedName;
            }

            return new FullyQualifiedName([$name]);
        });
    }

    public function resolveConstantName(Name|NameNode $name): FullyQualifiedName
    {
        return $this->doResolveName($name, function (UnqualifiedName $name): FullyQualifiedName {
            $imported = $this->constantImportTable->getName($name);

            if ($imported !== null) {
                return $imported;
            }

            if ($this->namespace === null) {
                return new FullyQualifiedName([$name]);
            }

            $namespacedName = new FullyQualifiedName([...$this->namespace->segments, $name]);

            if (($this->constantExists)($namespacedName->toStringWithoutSlash())) {
                return $namespacedName;
            }

            return new FullyQualifiedName([$name]);
        });
    }

    /**
     * @param list<Type> $arguments
     */
    public function resolveType(Name|NameNode $name, array $arguments = []): Type
    {
        if ($name instanceof NameNode) {
            $name = Name::fromNode($name);
        }

        if ($name instanceof UnqualifiedName) {
            $type = $this->mainImportTable->tryGetType($name, $arguments);

            if ($type !== null) {
                return $type;
            }
        }

        $className = $this->resolveClassName($name);

        if (!$className->lastSegment()->isConstantLike() || ($this->classExists)($className->toStringWithoutSlash())) {
            return types::object($className->toStringWithoutSlash(), ...$arguments);
        }

        return types::constant($this->resolveConstantName($name)->toStringWithoutSlash());
    }

    /**
     * @param \Closure(UnqualifiedName): FullyQualifiedName $unqualifiedResolver
     */
    private function doResolveName(Name|NameNode $name, \Closure $unqualifiedResolver): FullyQualifiedName
    {
        if ($name instanceof NameNode) {
            $name = Name::fromNode($name);
        }

        if ($name instanceof FullyQualifiedName) {
            return $name;
        }

        if ($name instanceof RelativeName) {
            return new FullyQualifiedName([...($this->namespace?->segments ?? []), ...$name->segments]);
        }

        if ($name instanceof QualifiedName) {
            $imported = $this->mainImportTable->getName($name->segments[0]);

            if ($imported !== null) {
                return new FullyQualifiedName([...$imported->segments, ...\array_slice($name->segments, 1)]);
            }

            return new FullyQualifiedName([...($this->namespace?->segments ?? []), ...$name->segments]);
        }

        if (!$name instanceof UnqualifiedName) {
            throw new \LogicException();
        }

        return $unqualifiedResolver($name);
    }
}
