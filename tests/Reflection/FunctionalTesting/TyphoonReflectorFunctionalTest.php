<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Typhoon\PhpStormReflectionStubs\PhpStormStubsLocator;
use Typhoon\Reflection\TyphoonReflector;

#[CoversNothing]
final class TyphoonReflectorFunctionalTest extends TestCase
{
    /**
     * @return \Generator<string, array{string}>
     */
    public static function files(): \Generator
    {
        foreach (Finder::create()->in(__DIR__ . '/tests')->name('*.php') as $file) {
            yield substr($file->getRelativePathname(), 0, -4) => [$file->getPathname()];
        }
    }

    #[DataProvider('files')]
    public function test(string $file): void
    {
        self::markTestSkipped('TODO');

        /** @psalm-suppress UnresolvableInclude */
        $test = require_once $file;
        \assert($test instanceof \Closure);
        $test(TyphoonReflector::build(locators: [new PhpStormStubsLocator()]));
    }
}
