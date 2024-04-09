<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangeDetectors::class)]
final class ChangeDetectorsTest extends TestCase
{
    public function testFromReturnsSingleChangeDetector(): void
    {
        $detector = new DeterministicChangeDetector('a', true);

        $actualDetector = ChangeDetectors::from([$detector]);

        self::assertSame($detector, $actualDetector);
    }

    public function testFromReturnsDeduplicatedChangeDetectors(): void
    {
        $a = new DeterministicChangeDetector('a', true);
        $a2 = new DeterministicChangeDetector('a', true);
        $b = new DeterministicChangeDetector('b', true);

        $deduplicated = ChangeDetectors::from([$a, $b, $a2])->deduplicate();

        self::assertSame($deduplicated, ['a' => $a2, 'b' => $b]);
    }

    public function testItDetectsChangeIfOneDetectorDetects(): void
    {
        $detector = ChangeDetectors::from([
            new DeterministicChangeDetector('a', false),
            new DeterministicChangeDetector('b', true),
        ]);

        $changed = $detector->changed();

        self::assertTrue($changed);
    }

    public function testItDoesNotDetectChangeIfNoneOfDetectorDetects(): void
    {
        $detector = ChangeDetectors::from([
            new DeterministicChangeDetector('a', false),
            new DeterministicChangeDetector('b', false),
        ]);

        $changed = $detector->changed();

        self::assertFalse($changed);
    }
}
