<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

final class DeterministicChangeDetector implements ChangeDetector
{
    /**
     * @param non-empty-string $id
     */
    public function __construct(
        private readonly string $id,
        private readonly bool $changed,
    ) {}

    public function changed(): bool
    {
        return $this->changed;
    }

    public function deduplicate(): array
    {
        return [$this->id => $this];
    }
}
