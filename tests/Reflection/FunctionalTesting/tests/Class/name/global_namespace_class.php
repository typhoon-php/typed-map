<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;
use function Typhoon\DeclarationId\anyClassId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php class A {}')[anyClassId('A')];

    assertSame('A', $reflection->name);
};
