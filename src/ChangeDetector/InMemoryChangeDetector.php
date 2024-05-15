<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class InMemoryChangeDetector implements ChangeDetector
{
    private bool $inMemory = true;

    public function changed(): bool
    {
        return !$this->inMemory;
    }

    public function deduplicate(): array
    {
        return [($this->inMemory ? 'true' : 'false') . '#in-memory' => $this];
    }

    public function __serialize(): array
    {
        return ['inMemory' => false];
    }
}
