<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;
use function Typhoon\DeclarationId\anonymousClassId;

return static function (TyphoonReflector $reflector): void {
    $object = new class () {};

    $reflection = $reflector->reflect(anonymousClassId(__FILE__, 12));

    assertSame($object::class, $reflection->name);
};
