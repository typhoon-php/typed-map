<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class InMemoryChangeDetector implements ChangeDetector
{
    private bool $changed = false;

    public function changed(): bool
    {
        return $this->changed;
    }

    public function deduplicate(): array
    {
        return [$this->hash() => $this];
    }

    /**
     * @return non-empty-string
     */
    private function hash(): string
    {
        return $this->changed . ':in-memory';
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $_data): void
    {
        $this->changed = true;
    }
}
