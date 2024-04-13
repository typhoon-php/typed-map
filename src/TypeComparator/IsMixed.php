<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use Typhoon\Type\Type;
use Typhoon\Type\Visitor\DefaultTypeVisitor;

/**
 * @internal
 * @psalm-internal Typhoon\TypeComparator
 * @extends DefaultTypeVisitor<bool>
 */
final class IsMixed extends DefaultTypeVisitor
{
    protected function default(Type $self): mixed
    {
        return true;
    }
}
