<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\Internal;

use Typhoon\DeclarationId\AliasId;
use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\TemplateId;
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
    public function getType(UnqualifiedName $name, array $arguments): ?Type
    {
        $import = $this->imports[$name->toLowerCaseString()] ?? null;

        if ($import instanceof \Closure) {
            return $import($arguments);
        }

        return null;
    }

    public function withName(Name $name, ?UnqualifiedName $alias): self
    {
        $table = clone $this;
        $name = $name->toFullyQualified();
        $table->imports[($alias ?? $name->lastSegment())->toLowerCaseString()] = $name;

        return $table;
    }

    /**
     * @param array<AliasId> $aliases
     */
    public function withAliases(array $aliases): self
    {
        $table = clone $this;

        foreach ($aliases as $alias) {
            $table->imports[strtolower($alias->name)] =
                /** @param list<Type> $arguments */
                static fn(array $arguments): Type => types::alias($alias, ...$arguments);
        }

        return $table;
    }

    /**
     * @param non-empty-array<TemplateId> $templates
     */
    public function withTemplates(array $templates): self
    {
        $table = clone $this;

        foreach ($templates as $template) {
            $table->imports[strtolower($template->name)] = static fn(): Type => types::template($template);
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

    public function atAnonymousClass(AnonymousClassId $id, ?FullyQualifiedName $parentName): self
    {
        $table = clone $this;

        $table->imports[Name::SELF] = $table->imports[Name::STATIC] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => types::object($id, ...$arguments);

        if ($parentName === null) {
            unset($table->imports[Name::PARENT]);
        } else {
            $table->imports[Name::PARENT] = $parentName;
        }

        return $table;
    }

    public function atTrait(FullyQualifiedName $name): self
    {
        $table = clone $this;
        $trait = $name->toStringWithoutSlash();
        $table->imports[Name::SELF] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => types::traitSelf($trait, ...$arguments);
        $table->imports[Name::PARENT] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => types::traitParent($trait, ...$arguments);
        $table->imports[Name::STATIC] =
            /** @param list<Type> $arguments */
            static fn(array $arguments): Type => types::traitStatic($trait, ...$arguments);

        return $table;
    }
}
