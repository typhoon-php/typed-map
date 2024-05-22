<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsNumericString extends Comparator
{
    public function stringValue(Type $self, string $value): mixed
    {
        return is_numeric($value);
    }

    public function numericString(Type $self): mixed
    {
        return true;
    }
}
