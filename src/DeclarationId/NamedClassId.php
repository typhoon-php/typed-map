<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class NamedClassId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return $this->name;
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionClass
    {
        return new \ReflectionClass($this->name);
    }
}
