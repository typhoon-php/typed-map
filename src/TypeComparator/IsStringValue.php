<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsStringValue extends Comparator
{
    public function __construct(
        private readonly string $value,
    ) {}

    public function stringValue(Type $self, string $value): mixed
    {
        return $this->value === $value;
    }
}
