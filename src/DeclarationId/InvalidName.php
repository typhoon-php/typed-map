<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class InvalidName extends \InvalidArgumentException
{
    public function __construct(string $declaration, string $name)
    {
        parent::__construct(sprintf('Invalid %s name "%s"', $declaration, $name));
    }
}
