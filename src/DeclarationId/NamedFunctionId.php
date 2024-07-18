<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @template-covariant TName of non-empty-string
 */
final class NamedFunctionId extends Id
{
    /**
     * @param TName $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    protected static function doFromReflection(\ReflectionFunction $reflection): self
    {
        return new self($reflection->name);
    }

    public function describe(): string
    {
        return sprintf('function %s()', $this->name);
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
