<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
final class IdNamedFunctionFactoryTest extends TestCase
{
    #[TestWith(['a'])]
    #[TestWith(['FUNCTION'])]
    #[TestWith(['func'])]
    #[TestWith(['Привет'])]
    public function testItAcceptsValidNames(string $name): void
    {
        $namedFunctionId = Id::namedFunction($name);

        self::assertSame($name, $namedFunctionId->name);
    }

    #[TestWith([''])]
    #[TestWith(['funct-'])]
    #[TestWith(['1funct'])]
    public function testItThrowsForInvalidNames(string $name): void
    {
        $this->expectExceptionObject(new InvalidName('function', $name));

        Id::namedFunction($name);
    }
}
