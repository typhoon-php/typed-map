<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

/**
 * @api
 */
final class TypeDeclarations
{
    /**
     * @param list<non-empty-string> $templateNames
     * @param list<non-empty-string> $aliasNames
     */
    public function __construct(
        public readonly array $templateNames = [],
        public readonly array $aliasNames = [],
    ) {}
}
