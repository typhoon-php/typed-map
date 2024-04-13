<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\Internal;

use Typhoon\Type\AtClass;
use Typhoon\Type\AtFunction;
use Typhoon\Type\AtMethod;
use Typhoon\Type\Type;
use Typhoon\Type\types;
use Typhoon\TypeContext\FullyQualifiedName;
use Typhoon\TypeContext\InvalidName;
use Typhoon\TypeContext\Name;
use Typhoon\TypeContext\UnqualifiedName;

/**
 * @internal
 * @psalm-internal Typhoon\TypeContext
 * @readonly
 */
final class MainImportTable
{
    /**
     * @var array<non-empty-lowercase-string, FullyQualifiedName|\Closure(list<Type>): Type>
     */
    private array $imports = [];

    public function getName(UnqualifiedName $alias): ?FullyQualifiedName
    {
        $import = $this->imports[$alias->toLowerCaseString()] ?? null;

        if ($import instanceof \Closure) {
            throw new InvalidName();
        }

        return $import;
    }

    /**
     * @param list<Type> $arguments
     */
    public function tryGetType(UnqualifiedName $name, array $arguments): ?Type
    {
        $import = $this->imports[$name->toLowerCaseString()] ?? null;

        if ($import instanceof \Closure) {
            return $import($arguments);
        }

        return null;
    }

    public function withName(Name $name, ?UnqualifiedName $alias = null): self
    {
        $table = clone $this;
        $name = $name->toFullyQualified();
        $table->imports[($alias ?? $name->lastSegment())->toLowerCaseString()] = $name;

        return $table;
    }

    /**
     * @param array<UnqualifiedName> $names
     * @param non-empty-string $class
     */
    public function withAliases(array $names, string $class): self
    {
        $table = clone $this;

        foreach ($names as $name) {
            $table->imports[$name->toLowerCaseString()] =
                /** @param list<Type> $arguments */
                static fn(array $arguments): Type => types::alias($name->toString(), $class, ...$arguments);
        }

        return $table;
    }

    /**
     * @param array<UnqualifiedName> $names
     */
    public function withTemplates(array $names, AtFunction|AtClass|AtMethod $declaredAt): self
    {
        $table = clone $this;

        foreach ($names as $name) {
            $table->imports[$name->toLowerCaseString()] =
                /** @param list<Type> $arguments */
                static fn(array $arguments): Type => types::template($name->toString(), $declaredAt, ...$arguments);
        }

        return $table;
    }

    public function atClass(FullyQualifiedName $name, ?FullyQualifiedName $parentName): self
    {
        $table = clone $this;

        $table->imports[Name::SELF] = $name;

        if ($parentName === null) {
            unset($table->imports[Name::PARENT]);
        } else {
            $table->imports[Name::PARENT] = $parentName;
        }

        $table->imports[Name::STATIC] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => types::static($name->toStringWithoutSlash(), ...$arguments);

        return $table;
    }

    public function atAnonymousClass(?FullyQualifiedName $parentName): self
    {
        $table = clone $this;

        $table->imports[Name::SELF] = $table->imports[Name::STATIC] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => new AnonymousSelfType($arguments);

        if ($parentName === null) {
            unset($table->imports[Name::PARENT]);
        } else {
            $table->imports[Name::PARENT] = $parentName;
        }

        return $table;
    }

    public function atTrait(FullyQualifiedName $name): self
    {
        $trait = $name->toStringWithoutSlash();
        $table = clone $this;
        $table->imports[Name::SELF] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => new TraitSelfType($trait, $arguments);
        $table->imports[Name::PARENT] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => new TraitParentType($trait, $arguments);
        $table->imports[Name::STATIC] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => new TraitStaticType($trait, $arguments);

        return $table;
    }
}
