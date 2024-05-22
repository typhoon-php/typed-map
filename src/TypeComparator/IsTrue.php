<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsTrue extends Comparator
{
    public function true(Type $self): mixed
    {
        return true;
    }

    public function constant(Type $self, string $name): mixed
    {
        return mb_strtolower($name) === 'true';
    }
}
