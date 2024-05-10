<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PackageChangeDetector::class)]
final class PackageChangeDetectorTest extends TestCase
{
    public function testFromPackageReturnsNullForNonInstalledPackage(): void
    {
        $detector = PackageChangeDetector::tryFromPackage('a/b');

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
            PackageChangeDetector::tryFromPackage('nikic/php-parser') ?? throw new \LogicException(),
            PackageChangeDetector::tryFromPackage('psr/simple-cache') ?? throw new \LogicException(),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertCount(2, $deduplicated);
    }

    /**
     * @param non-empty-string $package
     * @param non-empty-string $reference
     */
    private function newPackageDetector(string $package, string $reference): PackageChangeDetector
    {
        /**
         * @psalm-suppress InaccessibleMethod
         * @var \Closure(): PackageChangeDetector
         */
        $boundConstructor = (static fn(): PackageChangeDetector => new PackageChangeDetector($package, $reference))
            ->bindTo(null, PackageChangeDetector::class);

        return $boundConstructor();
    }
}
