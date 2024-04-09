<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileChangeDetector::class)]
final class FileChangeDetectorTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testItThrowsIfFileDoesNotExist(): void
    {
        $this->expectExceptionObject(new \RuntimeException('File "a.txt" does not exist or is not readable'));

        FileChangeDetector::fromFileAndContents('a.txt', 'a');
    }

    public function testItConsidersTouchedFileNotChanged(): void
    {
        $file = $this->root->url() . '/test.txt';
        $contents = 'test';
        file_put_contents($file, $contents);
        $detector = FileChangeDetector::fromFileAndContents($file, $contents);

        touch($file);

        self::assertFalse($detector->changed());
    }

    public function testItDetectsContentsChange(): void
    {
        $file = $this->root->url() . '/test.txt';
        $contents = 'test';
        file_put_contents($file, $contents);
        $detector = FileChangeDetector::fromFileAndContents($file, $contents);

        file_put_contents($file, 'new');

        self::assertTrue($detector->changed());
    }

    public function testItReturnsDeduplicatedDetectors(): void
    {
        $detector = ChangeDetectors::from([
            FileChangeDetector::fromFileAndContents(__FILE__, 'test'),
            FileChangeDetector::fromFileAndContents(__FILE__, 'test2'),
            FileChangeDetector::fromFileAndContents(__DIR__ . '/PackageChangeDetectorTest.php', ''),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertCount(2, $deduplicated);
    }
}
