<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class MethodId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly ClassId $class,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s::%s()', $this->class->toString(), $this->name);
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->class->equals($this->class)
            && $id->name === $this->name;
    }

    public function reflect(): \ReflectionMethod
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionMethod($this->class->name, $this->name);
    }
}
