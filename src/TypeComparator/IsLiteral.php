<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsLiteral extends Comparator
{
    public function __construct(
        private readonly Type $type,
    ) {}

    public function classConstant(Type $self, Type $class, string $name): mixed
    {
        return isSubtype($self, $this->type);
    }

    public function literal(Type $self, Type $type): mixed
    {
        return isSubtype($type, $this->type);
    }

    public function int(Type $self, ?int $min, ?int $max): mixed
    {
        return $min !== null && $max !== null && isSubtype($self, $this->type);
    }

    public function true(Type $self): mixed
    {
        return isSubtype($self, $this->type);
    }

    public function false(Type $self): mixed
    {
        return isSubtype($self, $this->type);
    }

    public function floatValue(Type $self, float $value): mixed
    {
        return isSubtype($self, $this->type);
    }

    public function stringValue(Type $self, string $value): mixed
    {
        return isSubtype($self, $this->type);
    }
}
