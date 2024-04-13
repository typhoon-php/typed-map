<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;

return (new TestBuilder())
    ->code('enum A: string {}')
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('A');

        assertFalse($reflection->isAbstractClass());
    });
