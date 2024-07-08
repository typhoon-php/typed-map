<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
final class IdConstantFactoryTest extends TestCase
{
    #[TestWith(['A'])]
    #[TestWith(['CONSTANT'])]
    #[TestWith(['Привет'])]
    public function testItAcceptsValidNames(string $name): void
    {
        $constantId = Id::constant($name);

        self::assertSame($name, $constantId->name);
    }

    #[TestWith([''])]
    #[TestWith(['const-'])]
    #[TestWith(['1const'])]
    public function testItThrowsForInvalidNames(string $name): void
    {
        $this->expectExceptionObject(new InvalidConstantName(sprintf('Invalid constant name "%s"', $name)));

        Id::constant($name);
    }
}
