<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Locator;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\ConstantId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\DeclarationId\NamedFunctionId;
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\Resource;

/**
 * @api
 */
final class AnonymousClassLocator implements Locator
{
    public function locate(ConstantId|NamedFunctionId|NamedClassId|AnonymousClassId $id): ?Resource
    {
        if ($id instanceof AnonymousClassId) {
            return Resource::fromFile($id->file);
        }

        return null;
    }
}
