<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
final class NamedFunctionId extends FunctionId
{
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
