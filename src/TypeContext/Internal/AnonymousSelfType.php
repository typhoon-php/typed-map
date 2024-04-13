<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\Internal;

use Typhoon\Type\Type;
use Typhoon\Type\TypeVisitor;
use Typhoon\TypeContext\AnonymousClassTypesResolver;

/**
 * @internal
 * @psalm-internal Typhoon\TypeContext
 * @readonly
 * @implements Type<object>
 */
final class AnonymousSelfType implements Type
{
    /**
     * @param list<Type> $arguments
     */
    public function __construct(
        private readonly array $arguments,
    ) {}

    /**
     * @template TReturn
     * @param TypeVisitor<TReturn> $visitor
     * @return TReturn
     */
    public function accept(TypeVisitor $visitor): mixed
    {
        if ($visitor instanceof AnonymousClassTypesResolver) {
            /** @var TReturn */
            return $visitor->objectType;
        }

        return $visitor->alias($this, 'self', 'anonymous-class', $this->arguments);
    }
}
