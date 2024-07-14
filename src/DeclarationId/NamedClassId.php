<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @template-covariant TName of non-empty-string
 */
final class NamedClassId extends Id
{
    /**
     * @param TName $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    /**
     * @return TName
     */
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

    public function jsonSerialize(): array
    {
        return [self::CODE_NAMED_CLASS, $this->name];
    }
}
