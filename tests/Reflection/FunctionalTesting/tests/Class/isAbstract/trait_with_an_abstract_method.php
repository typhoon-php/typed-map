<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertTrue;

return (new TestBuilder())
    ->code(
        <<<'PHP'
            trait A
            {
                abstract public function x(): void;
            }
            PHP,
    )
    ->test(static function (TyphoonReflector $reflector): void {
        $reflection = $reflector->reflectClass('A');

        assertTrue($reflection->isAbstract());
    });
