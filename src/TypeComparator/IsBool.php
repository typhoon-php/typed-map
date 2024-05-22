<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsBool extends Comparator
{
    public function bool(Type $self): mixed
    {
        return true;
    }

    public function true(Type $self): mixed
    {
        return true;
    }

    public function false(Type $self): mixed
    {
        return true;
    }
}
