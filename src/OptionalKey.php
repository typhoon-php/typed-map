<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 * @psalm-immutable
 * @template-covariant TValue
 * @extends Key<TValue>
 */
interface OptionalKey extends Key
{
    /**
     * @return TValue
     */
    public function default(TypedMap $map): mixed;
}
