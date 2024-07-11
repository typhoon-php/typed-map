<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class PropertyId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly NamedClassId|AnonymousClassId $class,
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

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->class->equals($this->class)
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionProperty
    {
        $class = $this->class->name ?? throw new \LogicException(sprintf(
            "Cannot reflect %s, because it's runtime name is not available",
            $this->toString(),
        ));

        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionProperty($class, $this->name);
    }
}
