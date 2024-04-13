<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\Internal;

use Typhoon\TypeContext\FullyQualifiedName;
use Typhoon\TypeContext\Name;
use Typhoon\TypeContext\UnqualifiedName;

/**
 * @internal
 * @psalm-internal Typhoon\TypeContext
 * @readonly
 */
final class ConstantImportTable
{
    /**
     * @var array<non-empty-string, FullyQualifiedName>
     */
    private array $names = [];

    public function getName(UnqualifiedName $alias): ?FullyQualifiedName
    {
        return $this->names[$alias->toString()] ?? null;
    }

    public function withName(Name $name, ?UnqualifiedName $alias = null): self
    {
        $table = clone $this;
        $name = $name->toFullyQualified();
        $table->names[($alias ?? $name->lastSegment())->toString()] = $name;

        return $table;
    }
}
