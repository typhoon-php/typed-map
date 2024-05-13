<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs;

use JetBrains\PHPStormStub\PhpStormStubsMap;
use Typhoon\ChangeDetector\ChangeDetector;
use Typhoon\ChangeDetector\PackageChangeDetector;
use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\ConstantId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\PhpStormReflectionStubs\Internal\ApplyTentativeTypeAttribute;
use Typhoon\PhpStormReflectionStubs\Internal\CleanUp;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\Resource;
use Typhoon\TypedMap\TypedMap;

/**
 * @api
 */
final class PhpStormStubsLocator implements Locator
{
    private const PACKAGE = 'jetbrains/phpstorm-stubs';

    /**
     * @var ?non-empty-string
     */
    private static ?string $directory = null;

    private static null|false|PackageChangeDetector $packageChangeDetector = false;

    private static function packageChangeDetector(): ?ChangeDetector
    {
        if (self::$packageChangeDetector === false) {
            return self::$packageChangeDetector = PackageChangeDetector::tryFromPackage(self::PACKAGE);
        }

        return self::$packageChangeDetector;
    }

    /**
     * @return non-empty-string
     */
    private static function directory(): string
    {
        if (self::$directory !== null) {
            return self::$directory;
        }

        if (\defined(PhpStormStubsMap::class . '::DIR')) {
            return self::$directory = PhpStormStubsMap::DIR;
        }

        $file = (new \ReflectionClass(PhpStormStubsMap::class))->getFileName();
        \assert($file !== false, sprintf('Failed to locate class %s', PhpStormStubsMap::class));

        return self::$directory = \dirname($file);
    }

    public function locate(ConstantId|FunctionId|ClassId $id): ?Resource
    {
        $relativePath = match (true) {
            $id instanceof ConstantId => PhpStormStubsMap::CONSTANTS[$id->name] ?? null,
            $id instanceof FunctionId => PhpStormStubsMap::FUNCTIONS[$id->name] ?? null,
            $id instanceof ClassId => PhpStormStubsMap::CLASSES[$id->name] ?? null,
        };

        if ($relativePath === null) {
            return null;
        }

        $baseData = (new TypedMap())
            ->set(Data::Extension(), \dirname($relativePath))
            ->set(Data::WrittenInC(), true);

        $packageChangeDetector = self::packageChangeDetector();

        if ($packageChangeDetector !== null) {
            $baseData = $baseData->set(Data::UnresolvedChangeDetectors(), [$packageChangeDetector]);
        }

        return new Resource(
            file: self::directory() . '/' . $relativePath,
            baseData: $baseData,
            hooks: [
                new ApplyTentativeTypeAttribute(),
                new CleanUp(),
            ],
        );
    }
}
