<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class DeterministicChangeDetector implements ChangeDetector
{
    /**
     * @param non-empty-string $hash
     */
    public function __construct(
        private readonly string $hash,
        private readonly bool $changed,
    ) {}

    public function changed(): bool
    {
        return $this->changed;
    }

    public function deduplicate(): array
    {
        return [$this->hash => $this];
    }
}
