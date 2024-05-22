<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsFloatValue extends Comparator
{
    public function __construct(
        private readonly float $value,
    ) {}

    public function floatValue(Type $self, float $value): mixed
    {
        return $this->value === $value;
    }
}
