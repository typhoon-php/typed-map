<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class IfSerializedChangeDetector implements ChangeDetector
{
    private bool $serialized = false;

    public function changed(): bool
    {
        return $this->serialized;
    }

    public function deduplicate(): array
    {
        return [($this->serialized ? 'true' : 'false') . '#serialized' => $this];
    }

    public function __serialize(): array
    {
        return ['serialized' => true];
    }
}
