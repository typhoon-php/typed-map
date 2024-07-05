<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class NamedFunctionId extends FunctionId
{
    protected static function doFromReflection(\ReflectionFunction $reflection): self
    {
        \assert($reflection->name !== '');

        return new self($reflection->name);
    }

    public function toString(): string
    {
        return $this->name . '()';
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->name === $this->name;
    }
}