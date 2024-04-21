<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

/**
 * @api
 * @readonly
 * @psalm-import-type Exists from TypeContext
 */
final class PreResolvedConstantName
{
    public function __construct(
        public readonly FullyQualifiedName $name,
        public readonly ?FullyQualifiedName $global = null,
    ) {}

    /**
     * @param ?callable(non-empty-string): bool $constantExists
     */
    public function resolve(?callable $constantExists = null): FullyQualifiedName
    {
        if ($this->global === null || ($constantExists ?? 'defined')($this->name->toStringWithoutSlash())) {
            return $this->name;
        }

        return $this->name->lastSegment()->toFullyQualified();
    }
}
