<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
abstract class FunctionId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}
}
