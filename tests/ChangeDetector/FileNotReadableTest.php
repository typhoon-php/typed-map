<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileNotReadable::class)]
final class FileNotReadableTest extends TestCase
{
    public function testItComposesCorrectMessage(): void
    {
        $exception = new FileNotReadable('a.txt');

        self::assertSame('File "a.txt" does not exist or is not readable', $exception->getMessage());
    }
}
