<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 * @template-covariant TValue = mixed
 */
final readonly class Pair
{
    /**
     * @param Key<TValue> $key
     * @param TValue $value
     */
    public function __construct(
        public Key $key,
        public mixed $value,
    ) {}
}
