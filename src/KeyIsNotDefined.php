<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

use function Typhoon\Formatter\format;

/**
 * @api
 */
final class KeyIsNotDefined extends \RuntimeException
{
    /**
     * @param Key<*> $key
     */
    public function __construct(Key $key)
    {
        parent::__construct(\sprintf('Key %s is not defined in the TypedMap', format($key)));
    }
}
