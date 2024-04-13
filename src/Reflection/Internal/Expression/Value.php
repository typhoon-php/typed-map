<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\Expression;

use Typhoon\Reflection\Reflector;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 */
final class Value implements Expression
{
    public function __construct(
        private readonly null|bool|int|float|string|array $value,
    ) {}

    public function evaluate(Reflector $reflector): mixed
    {
        return $this->value;
    }
}
