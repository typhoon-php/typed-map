<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileIsNotReadable::class)]
final class FileIsNotReadableTest extends TestCase
{
    public function testItComposesCorrectMessage(): void
    {
        $exception = new FileIsNotReadable('a.txt');

        self::assertSame('File "a.txt" does not exist or is not readable', $exception->getMessage());
    }
}
