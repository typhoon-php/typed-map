<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;
use Typhoon\Type\types;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsNonEmpty extends Comparator
{
    public function __construct(
        private readonly Type $type,
    ) {}

    public function true(Type $self): mixed
    {
        return isSubtype($self, $this->type);
    }

    public function int(Type $self, ?int $min, ?int $max): mixed
    {
        return !isSubtype($self, types::int(0, 0)) && isSubtype($self, $this->type);
    }

    public function floatValue(Type $self, float $value): mixed
    {
        return $value !== 0.0 && isSubtype($self, $this->type);
    }

    public function stringValue(Type $self, string $value): mixed
    {
        return $value !== '' && isSubtype($self, $this->type);
    }

    public function nonEmpty(Type $self, Type $type): mixed
    {
        return isSubtype($self, $this->type);
    }
}
