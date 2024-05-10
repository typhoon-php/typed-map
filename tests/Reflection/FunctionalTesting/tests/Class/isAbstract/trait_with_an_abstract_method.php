<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertTrue;
use function Typhoon\DeclarationId\anyClassId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            trait A
            {
                abstract public function x(): void;
            }
            PHP,
    )[anyClassId('A')];

    assertTrue($reflection->isAbstract());
};
