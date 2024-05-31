<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
abstract class ClassId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    /**
     * @psalm-suppress InaccessibleProperty
     * @param non-empty-string $name
     */
    final protected function setName(string $name): void
    {
        $this->name = $name;
    }
}
