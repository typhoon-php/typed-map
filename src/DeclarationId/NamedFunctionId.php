<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class NamedFunctionId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    protected static function doFromReflection(\ReflectionFunction $reflection): self
    {
        \assert($reflection->name !== '');

        return new self($reflection->name);
    }

    public function toString(): string
    {
        return $this->name . '()';
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionFunction
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionFunction($this->name);
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_NAMED_FUNCTION, $this->name];
    }
}
