<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
function classId(string|object $nameOrObject): ClassId
{
    return DeclarationId::class($nameOrObject);
}

/**
 * @api
 */
function namedClassId(string|object $nameOrObject): NamedClassId
{
    return DeclarationId::namedClass($nameOrObject);
}
