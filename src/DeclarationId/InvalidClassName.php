<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class InvalidClassName extends \InvalidArgumentException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(str_replace("\x00", '\0', $message), $code, $previous);
    }
}
