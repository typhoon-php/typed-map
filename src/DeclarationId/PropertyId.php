<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class PropertyId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly ClassId $class,
        public readonly string $name,
    ) {}

    protected static function doFromReflection(\ReflectionProperty $property): self
    {
        \assert($property->name !== '');

        return new self(self::fromReflection($property->getDeclaringClass()), $property->name);
    }

    public function toString(): string
    {
        return sprintf('%s::$%s', $this->class->toString(), $this->name);
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->class->equals($this->class)
            && $id->name === $this->name;
    }

    public function reflect(): \ReflectionProperty
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionProperty($this->class->name, $this->name);
    }
}
