<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;

return static function (TyphoonReflector $reflector): void {
    $object = new class () {};

    $reflection = $reflector->reflectClass($object);

    assertSame($object::class, $reflection->name);
};
