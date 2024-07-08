<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
abstract class FunctionId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly string $name,
    ) {}

    final public function reflect(): \ReflectionFunction
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        return new \ReflectionFunction($this->name);
    }
}
