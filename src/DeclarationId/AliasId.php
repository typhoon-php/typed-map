<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AliasId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly NamedClassId|AnonymousClassId $class,
        public readonly string $name,
    ) {}

    public function describe(): string
    {
        return \sprintf('type alias %s of %s', $this->name, $this->class->describe());
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->class->equals($this->class)
            && $value->name === $this->name;
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_ALIAS, $this->class, $this->name];
    }
}
