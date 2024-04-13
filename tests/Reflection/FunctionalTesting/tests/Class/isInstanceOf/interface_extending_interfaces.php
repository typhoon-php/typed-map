<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return (new TestBuilder())
    ->code(
        <<<'PHP'
            interface I1 {}
            interface I2 extends I1 {}
            PHP,
    )
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('I2');

        assertFalse($reflection->isInstanceOf(\Iterator::class));
        assertFalse($reflection->isInstanceOf(\stdClass::class));
        assertTrue($reflection->isInstanceOf('I2'));
        assertTrue($reflection->isInstanceOf('I1'));
    });
