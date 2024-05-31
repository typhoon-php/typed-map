<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\DeclarationId\ConstantId;
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

    public function constant(Type $self, ConstantId $constant): mixed
    {
        return mb_strtolower($constant->name) === 'false';
    }
}
