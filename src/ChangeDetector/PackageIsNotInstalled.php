<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class PackageIsNotInstalled extends \RuntimeException
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name, ?\Throwable $previous = null)
    {
        parent::__construct(\sprintf('Package "%s" is not installed via Composer', $name), previous: $previous);
    }
}
