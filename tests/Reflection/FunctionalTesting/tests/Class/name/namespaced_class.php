<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            namespace X\Y;
            
            class A {}
            PHP,
    )[Id::namedClass('X\Y\A')];

    assertSame('X\Y\A', $reflection->name);
};
