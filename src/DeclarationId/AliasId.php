<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AliasId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly NamedClassId $class,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s@%s', $this->name, $this->class->toString());
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->class->equals($this->class)
            && $id->name === $this->name;
    }
}
