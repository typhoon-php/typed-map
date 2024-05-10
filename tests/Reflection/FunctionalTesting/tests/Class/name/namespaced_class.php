<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;
use function Typhoon\DeclarationId\anyClassId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            namespace X\Y;
            
            class A {}
            PHP,
    )[anyClassId('X\Y\A')];

    assertSame('X\Y\A', $reflection->name);
};
