<?php

declare(strict_types=1);

namespace PHP\ExtendedTypeSystem\TypeReflection;

/**
 * @psalm-api
 * @psalm-immutable
 */
enum Variance
{
    case INVARIANT;
    case COVARIANT;
    case CONTRAVARIANT;
}
