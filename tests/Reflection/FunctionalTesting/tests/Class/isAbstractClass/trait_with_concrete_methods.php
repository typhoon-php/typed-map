<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            trait A
            {
                public function x(): void {}
            }
            PHP,
    )[Id::namedClass('A')];

    assertFalse($reflection->isAbstractClass());
};
