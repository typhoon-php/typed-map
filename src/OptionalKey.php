<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 * @template-covariant TValue = mixed
 * @extends Key<TValue>
 */
interface OptionalKey extends Key
{
    /**
     * @return TValue
     */
    public function default(TypedMap $map): mixed;
}
