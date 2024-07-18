<?php

declare(strict_types=1);

namespace Typhoon\Type\Internal;

use Typhoon\Type\Type;
use Typhoon\Type\TypeVisitor;

/**
 * @internal
 * @psalm-internal Typhoon\Type
 * @psalm-immutable
 * @implements Type<mixed>
 */
final class ArgumentType implements Type
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public readonly string $name,
    ) {}

    public function accept(TypeVisitor $visitor): mixed
    {
        return $visitor->argument($this, $this->name);
    }
}
