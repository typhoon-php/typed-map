<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposerPackageChangeDetector::class)]
final class PackageChangeDetectorTest extends TestCase
{
    public function testFromPackageReturnsNullForNonInstalledPackage(): void
    {
        $detector = ComposerPackageChangeDetector::tryFromName('a/b');

        self::assertNull($detector);
    }

    public function testItDetectsPackageRefChange(): void
    {
        $changeDetector = $this->newPackageDetector('nikic/php-parser', 'fake-ref');

        $changed = $changeDetector->changed();

        self::assertTrue($changed);
    }

    public function testItReturnsDeduplicatedDetectors(): void
    {
        $detector = ChangeDetectors::from([
            $this->newPackageDetector('nikic/php-parser', '0.3.0'),
            ComposerPackageChangeDetector::tryFromName('nikic/php-parser') ?? throw new \LogicException(),
            ComposerPackageChangeDetector::tryFromName('psr/simple-cache') ?? throw new \LogicException(),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertCount(3, $deduplicated);
    }

    /**
     * @param non-empty-string $package
     * @param non-empty-string $reference
     */
    private function newPackageDetector(string $package, string $reference): ComposerPackageChangeDetector
    {
        /**
         * @psalm-suppress InaccessibleMethod
         * @var \Closure(): ComposerPackageChangeDetector
         */
        $boundConstructor = (static fn(): ComposerPackageChangeDetector => new ComposerPackageChangeDetector($package, $reference))
            ->bindTo(null, ComposerPackageChangeDetector::class);

        return $boundConstructor();
    }
}
