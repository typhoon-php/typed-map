<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
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
                 * @return 'a'
                 */
                public function a(): string {}
            }
            
            class B extends A
            {
            }
            PHP,
    )[classId('B')]->method('a') ?? throw new \LogicException();

    assertEquals(types::string, $reflection->returnType(Kind::Native));
    assertEquals(types::literalValue('a'), $reflection->returnType(Kind::Annotated));
    assertEquals(types::literalValue('a'), $reflection->returnType(Kind::Resolved));
};
