<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsString extends Comparator
{
    public function classConstant(Type $self, Type $class, string $name): mixed
    {
        // TODO full class constant support.
        return $name === 'class';
    }

    public function classString(Type $self, Type $class): mixed
    {
        return true;
    }

    public function stringValue(Type $self, string $value): mixed
    {
        return true;
    }

    public function numericString(Type $self): mixed
    {
        return true;
    }

    public function string(Type $self): mixed
    {
        return true;
    }

    public function truthyString(Type $self): mixed
    {
        return true;
    }
}
