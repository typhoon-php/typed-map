<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Locator;

use Composer\Autoload\ClassLoader;
use Typhoon\DeclarationId\ConstantId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\Resource;

/**
 * @api
 */
final class ComposerLocator implements Locator
{
    public static function isSupported(): bool
    {
        return class_exists(ClassLoader::class);
    }

    public function locate(ConstantId|FunctionId|NamedClassId $id): ?Resource
    {
        if (!$id instanceof NamedClassId) {
            return null;
        }

        foreach (ClassLoader::getRegisteredLoaders() as $loader) {
            $file = $loader->findFile($id->name);

            if ($file !== false) {
                return new Resource($file);
            }
        }

        return null;
    }
}
