<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

/**
 * @api
 */
final class FileIsNotReadable extends \RuntimeException
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(string $path, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('File "%s" does not exist or is not readable', $path), previous: $previous);
    }
}
