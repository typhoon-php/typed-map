<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

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
    private MainImportTable $mainImportTable;

    private FunctionImportTable $functionImportTable;

    private ConstantImportTable $constantImportTable;

    /**
     * @param Exists $classExists
     * @param Exists $functionExists
     * @param Exists $constantExists
     */
    public function __construct(
        private readonly ?FullyQualifiedName $namespace,
        private readonly mixed $classExists,
        private readonly mixed $functionExists,
        private readonly mixed $constantExists,
    ) {
        $this->mainImportTable = new MainImportTable();
        $this->functionImportTable = new FunctionImportTable();
        $this->constantImportTable = new ConstantImportTable();
    }

    public function withUse(Name $name, ?UnqualifiedName $alias = null): self
    {
        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->withName($name, $alias);

        return $context;
    }

    public function withFunctionUse(Name $name, ?UnqualifiedName $alias = null): self
    {
        $context = clone $this;
        $context->functionImportTable = $context->functionImportTable->withName($name, $alias);

        return $context;
    }

    public function withConstantUse(Name $name, ?UnqualifiedName $alias = null): self
    {
        $context = clone $this;
        $context->constantImportTable = $context->constantImportTable->withName($name, $alias);

        return $context;
    }

    public function atClass(FullyQualifiedName $name, ?FullyQualifiedName $parentName = null): self
    {
        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->atClass($name, $parentName);

        return $context;
    }

    public function atAnonymousClass(?FullyQualifiedName $parentName = null): self
    {
        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->atAnonymousClass($parentName);

        return $context;
    }

    public function atTrait(FullyQualifiedName $name): self
    {
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
    public function withTemplates(array $names, AtFunction|AtClass|AtMethod $declaredAt): self
    {
        if ($names === []) {
            return $this;
        }

        $context = clone $this;
        $context->mainImportTable = $context->mainImportTable->withTemplates($names, $declaredAt);

        return $context;
    }

    public function resolveDeclaredName(UnqualifiedName $name): FullyQualifiedName
    {
        return $name->resolveAsClassName($this->namespace, new MainImportTable());
    }

    public function resolveClassName(Name $name): FullyQualifiedName
    {
        return $name->resolveAsClassName($this->namespace, $this->mainImportTable);
    }

    public function resolveFunctionName(Name $name): FullyQualifiedName
    {
        return $name->resolveAsFunctionName($this->namespace, $this->mainImportTable, $this->functionImportTable, $this->functionExists);
    }

    public function resolveConstantName(Name $name): FullyQualifiedName
    {
        return $name->resolveAsConstantName($this->namespace, $this->mainImportTable, $this->constantImportTable, $this->constantExists);
    }

    /**
     * @param list<Type> $arguments
     */
    public function resolveType(Name $name, array $arguments = []): Type
    {
        if ($name instanceof UnqualifiedName) {
            $type = $this->mainImportTable->tryGetType($name, $arguments);

            if ($type !== null) {
                return $type;
            }
        }

        $className = $this->resolveClassName($name);

        if (!$className->lastSegment()->isConstantLike() || ($this->classExists)($className->toString())) {
            return types::object($className->toString(), ...$arguments);
        }

        return types::constant($this->resolveConstantName($name)->toString());
    }
}