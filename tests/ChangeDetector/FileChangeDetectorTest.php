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

    public function testFromPathEnsureExistsThrowsIfFileDoesNotExist(): void
    {
        $this->expectExceptionObject(new FileIsNotReadable('a.txt'));

        FileChangeDetector::fromPathEnsureExists('a.txt');
    }

    public function testItConsidersTouchedFileNotChanged(): void
    {
        $file = $this->root->url() . '/test.txt';
        file_put_contents($file, 'content');
        $detector = FileChangeDetector::fromPath($file);

        touch($file);

        self::assertFalse($detector->changed());
    }

    public function testItDetectsContentsChange(): void
    {
        $file = $this->root->url() . '/test.txt';
        file_put_contents($file, 'content');
        $detector = FileChangeDetector::fromPath($file);

        file_put_contents($file, 'new');

        self::assertTrue($detector->changed());
    }

    public function testItReturnsDeduplicatedDetectors(): void
    {
        $detector = ChangeDetectors::from([
            new FileChangeDetector('test1', 1, 'a'),
            new FileChangeDetector('test2', 2, 'b'),
            new FileChangeDetector('test1', 3, 'c'),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertCount(3, $deduplicated);
    }
}
