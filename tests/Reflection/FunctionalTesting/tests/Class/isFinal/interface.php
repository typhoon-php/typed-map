<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;

return (new TestBuilder())
    ->code('interface A {}')
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('A');

        assertFalse($reflection->isFinal(Kind::Native));
        assertFalse($reflection->isFinal(Kind::Annotated));
        assertFalse($reflection->isFinal());
    });
