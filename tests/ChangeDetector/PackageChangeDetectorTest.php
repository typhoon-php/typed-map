<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\PhpProcess;
use Symfony\Component\Process\Process;

#[CoversClass(PackageChangeDetector::class)]
final class PackageChangeDetectorTest extends TestCase
{
    private const TEMP_DIR = __DIR__ . '/../../var/PackageChangeDetectorTest';

    public static function setUpBeforeClass(): void
    {
        (new Filesystem())->mkdir(self::TEMP_DIR);
    }

    public function testFromPackageReturnsNullForNonInstalledPackage(): void
    {
        $detector = PackageChangeDetector::tryFromPackage('a/b');

        self::assertNull($detector);
    }

    #[RunInSeparateProcess]
    public function testItDetectsPackageCommitChange(): void
    {
        self::markTestSkipped('Should be refactored');

        $this->requirePsrLog('dev-master#4165cf6c29d0b7f34807590b2718caf483e8b1f7');
        $this->saveDetector();

        $this->requirePsrLog('dev-master#ce57d91bc60d908d432ab22d276619789d1d908d');
        /** @psalm-suppress UnresolvableInclude */
        require_once self::TEMP_DIR . '/vendor/autoload.php';
        $changed = $this->getDetector()->changed();

        self::assertTrue($changed);
    }

    #[Depends('testItDetectsPackageCommitChange')]
    public function testItReturnsDeduplicatedDetectors(): void
    {
        self::markTestSkipped('Should be refactored');

        $detector = ChangeDetectors::from([
            new PackageChangeDetector('nikic/php-parser', '0.3.0'),
            PackageChangeDetector::tryFromPackage('nikic/php-parser') ?? throw new \LogicException(),
            PackageChangeDetector::tryFromPackage('psr/simple-cache') ?? throw new \LogicException(),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertCount(2, $deduplicated);
    }

    private function requirePsrLog(string $constraint): void
    {
        (new Process(['composer', 'req', 'psr/log', $constraint], cwd: self::TEMP_DIR))->mustRun();
    }

    private function getDetector(): PackageChangeDetector
    {
        $detector = unserialize(file_get_contents(self::TEMP_DIR . '/detector'));
        \assert($detector instanceof PackageChangeDetector);

        return $detector;
    }

    private function saveDetector(): void
    {
        (new PhpProcess(
            <<<'PHP'
                <?php
                
                require_once __DIR__.'/vendor/autoload.php';
                require_once __DIR__.'/../../src/ChangeDetector/ChangeDetector.php';
                require_once __DIR__.'/../../src/ChangeDetector/PackageChangeDetector.php';
                
                use Typhoon\ChangeDetector\PackageChangeDetector;
                
                file_put_contents(__DIR__.'/detector', serialize(PackageChangeDetector::tryFromPackage('psr/log')));
                PHP,
            cwd: self::TEMP_DIR,
        ))->mustRun();
    }
}
