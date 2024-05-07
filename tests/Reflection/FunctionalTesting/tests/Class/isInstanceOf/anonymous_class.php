<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use function Typhoon\DeclarationId\anonymousClassId;

return static function (TyphoonReflector $reflector): void {
    TestCase::markTestSkipped();

    $object = new class () extends \RuntimeException {};

    $reflection = $reflector->reflectClass($object);

    assertFalse($reflection->isInstanceOf(\Iterator::class));
    assertFalse($reflection->isInstanceOf(\stdClass::class));
    assertTrue($reflection->isInstanceOf($object::class));
    assertTrue($reflection->isInstanceOf(anonymousClassId(__FILE__, 14)));
};
