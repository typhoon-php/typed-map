<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 */
final class KeyIsNotDefined extends \RuntimeException
{
    public function __construct(Key $key)
    {
        parent::__construct(\sprintf('Key %s::%s is not defined in the TypedMap', $key::class, $key->name));
    }
}
