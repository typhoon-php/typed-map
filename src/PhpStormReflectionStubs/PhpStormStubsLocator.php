<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs;

use JetBrains\PHPStormStub\PhpStormStubsMap;
use Typhoon\ChangeDetector\ChangeDetector;
use Typhoon\ChangeDetector\ComposerPackageChangeDetector;
use Typhoon\ChangeDetector\FileChangeDetector;
use Typhoon\DeclarationId\ConstantId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\DeclarationId\NamedFunctionId;
use Typhoon\PhpStormReflectionStubs\Internal\ApplyTentativeTypeAttribute;
use Typhoon\PhpStormReflectionStubs\Internal\CleanUp;
use Typhoon\Reflection\Internal\Data\Data;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Reflection\Locator\ConstantLocator;
use Typhoon\Reflection\Locator\NamedClassLocator;
use Typhoon\Reflection\Locator\NamedFunctionLocator;
use Typhoon\Reflection\Resource;

/**
 * @api
 */
final class PhpStormStubsLocator implements ConstantLocator, NamedFunctionLocator, NamedClassLocator
{
    private const PACKAGE = 'jetbrains/phpstorm-stubs';

    /**
     * @var ?non-empty-string
     */
    private static ?string $directory = null;

    private static null|false|ComposerPackageChangeDetector $packageChangeDetector = false;

    private static function packageChangeDetector(): ?ChangeDetector
    {
        if (self::$packageChangeDetector === false) {
            return self::$packageChangeDetector = ComposerPackageChangeDetector::tryFromName(self::PACKAGE);
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

    public function locate(ConstantId|NamedFunctionId|NamedClassId $id): ?Resource
    {
        $relativePath = match (true) {
            $id instanceof ConstantId => PhpStormStubsMap::CONSTANTS[$id->name] ?? null,
            $id instanceof NamedFunctionId => PhpStormStubsMap::FUNCTIONS[$id->name] ?? null,
            $id instanceof NamedClassId => PhpStormStubsMap::CLASSES[$id->name] ?? null,
        };

        if ($relativePath === null) {
            return null;
        }

        $file = self::directory() . '/' . $relativePath;
        $code = Resource::readFile(self::directory() . '/' . $relativePath);
        $packageChangeDetector = self::packageChangeDetector();

        return new Resource(
            code: $code,
            baseData: (new TypedMap())
                ->set(Data::PhpExtension, \dirname($relativePath))
                ->set(Data::InternallyDefined, true)
                ->set(Data::UnresolvedChangeDetectors, [
                    $packageChangeDetector ?? FileChangeDetector::fromFileAndContents($file, $code),
                ]),
            hooks: [
                new ApplyTentativeTypeAttribute(),
                new CleanUp(),
            ],
        );
    }
}
