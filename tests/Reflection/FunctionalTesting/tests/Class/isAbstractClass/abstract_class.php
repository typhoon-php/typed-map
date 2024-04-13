<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertTrue;

return (new TestBuilder())
    ->code('abstract class A {}')
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('A');

        assertTrue($reflection->isAbstractClass());
    });
