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
        if ($property->name === '') {
            // https://github.com/vimeo/psalm/pull/10091#issuecomment-1670027553
            throw new \InvalidArgumentException('Dynamic property identification is not supported');
        }

        return new self(self::fromReflection($property->getDeclaringClass()), $property->name);
    }

    public function describe(): string
    {
        if ($this->class instanceof AnonymousClassId) {
            return \sprintf('property $%s of %s', $this->name, $this->class->describe());
        }

        return \sprintf('property %s::$%s', $this->class->name, $this->name);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->class->equals($this->class)
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionProperty
    {
        $class = $this->class->name ?? throw new \LogicException(\sprintf(
            "Cannot reflect %s, because it's runtime name is not available",
            $this->describe(),
        ));

        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionProperty($class, $this->name);
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_PROPERTY, $this->class, $this->name];
    }
}
