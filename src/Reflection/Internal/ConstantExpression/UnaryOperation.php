<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\ConstantExpression;

use Typhoon\Reflection\Reflector;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 * @implements Expression<mixed>
 */
final class UnaryOperation implements Expression
{
    /**
     * @param non-empty-string $operator
     */
    public function __construct(
        private readonly Expression $expression,
        private readonly string $operator,
    ) {}

    public function evaluate(?Reflector $reflector = null): mixed
    {
        return match ($this->operator) {
            '+' => +$this->expression->evaluate($reflector),
            '-' => -$this->expression->evaluate($reflector),
            '!' => !$this->expression->evaluate($reflector),
            '~' => ~$this->expression->evaluate($reflector),
        };
    }
}
