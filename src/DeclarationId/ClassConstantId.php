<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class ClassConstantId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly NamedClassId|AnonymousClassId $class,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s::%s', $this->class->toString(), $this->name);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->class->equals($this->class)
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionClassConstant
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionClassConstant($this->class->name, $this->name);
    }
}
