<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

/**
 * @api
 * @readonly
 * @psalm-import-type Exists from TypeContext
 */
final class PreResolvedFunctionName
{
    public function __construct(
        public readonly FullyQualifiedName $name,
        public readonly ?FullyQualifiedName $global = null,
    ) {}

    /**
     * @param ?callable(non-empty-string): bool $functionExists
     */
    public function resolve(?callable $functionExists = null): FullyQualifiedName
    {
        if ($this->global === null || ($functionExists ?? 'function_exists')($this->name->toStringWithoutSlash())) {
            return $this->name;
        }

        return $this->name->lastSegment()->toFullyQualified();
    }
}
