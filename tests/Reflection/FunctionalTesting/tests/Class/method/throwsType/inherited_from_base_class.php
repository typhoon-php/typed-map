<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\types;
use function PHPUnit\Framework\assertEquals;
use function Typhoon\DeclarationId\classId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            abstract class A
            {
                /**
                 * @throws LogicException|RuntimeException
                 * @throws Exception
                 */
                public function a(): string {}
            }
            
            class B extends A
            {
            }
            PHP,
    )[classId('B')]->method('a') ?? throw new \LogicException();

    assertEquals(
        types::union(
            types::union(types::object(\LogicException::class), types::object(\RuntimeException::class)),
            types::object(\Exception::class),
        ),
        $reflection->throwsType(),
    );
};
