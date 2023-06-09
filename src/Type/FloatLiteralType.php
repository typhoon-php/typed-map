<?php

declare(strict_types=1);

namespace ExtendedTypeSystem\Type;

use ExtendedTypeSystem\Type;
use ExtendedTypeSystem\TypeVisitor;

/**
 * @api
 * @psalm-immutable
 * @template-covariant TValue of float
 * @implements Type<TValue>
 */
final class FloatLiteralType implements Type
{
    /**
     * @var TValue
     */
    public readonly float $value;

    /**
     * @internal
     * @psalm-internal ExtendedTypeSystem
     * @param TValue $value
     */
    public function __construct(
        float $value,
    ) {
        $this->value = $value;
    }

    public function accept(TypeVisitor $visitor): mixed
    {
        return $visitor->visitFloatLiteral($this);
    }
}
