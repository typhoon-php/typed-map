<?php

declare(strict_types=1);

namespace Typhoon\ChangeDetector;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeterministicChangeDetector::class)]
final class DeterministicChangeDetectorTest extends TestCase
{
    public function testItIsChangedIfChangedTrue(): void
    {
        $detector = new DeterministicChangeDetector('test', true);

        $changed = $detector->changed();

        self::assertTrue($changed);
    }

    public function testItIsNotChangedIfChangedFalse(): void
    {
        $detector = new DeterministicChangeDetector('test', false);

        $changed = $detector->changed();

        self::assertFalse($changed);
    }

    public function testItIsDeduplicatedById(): void
    {
        $detector = ChangeDetectors::from([
            new DeterministicChangeDetector('test', false),
            $detector1 = new DeterministicChangeDetector('test', true),
            new DeterministicChangeDetector('test2', false),
            $detector2 = new DeterministicChangeDetector('test2', true),
        ]);

        $deduplicated = $detector->deduplicate();

        self::assertSame(['test' => $detector1, 'test2' => $detector2], $deduplicated);
    }
}
