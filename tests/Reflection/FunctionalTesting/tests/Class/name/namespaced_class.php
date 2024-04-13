<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;

return (new TestBuilder())
    ->code('namespace X\Y; class A {}')
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('X\Y\A');

        assertSame('X\Y\A', $reflection->name);
    });
