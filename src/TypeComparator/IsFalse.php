<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 */
final class IsFalse extends Comparator
{
    public function false(Type $self): mixed
    {
        return false;
    }

    public function constant(Type $self, string $name): mixed
    {
        return mb_strtolower($name) === 'false';
    }
}
