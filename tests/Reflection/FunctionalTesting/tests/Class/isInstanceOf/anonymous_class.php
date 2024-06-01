<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\DeclarationId;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return static function (TyphoonReflector $reflector): void {
    $object = new class () extends \RuntimeException {};

    $reflection = $reflector->reflectClass($object);

    assertFalse($reflection->isInstanceOf(\Iterator::class));
    assertFalse($reflection->isInstanceOf(\stdClass::class));
    assertTrue($reflection->isInstanceOf($object::class));
    assertTrue($reflection->isInstanceOf(DeclarationId::anonymousClass(__FILE__, 13, 19)));
};
