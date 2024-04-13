<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return (new TestBuilder())
    ->code('enum A: string {}')
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('A');

        assertTrue($reflection->isFinal(Kind::Native));
        assertFalse($reflection->isFinal(Kind::Annotated));
        assertTrue($reflection->isFinal());
    });
