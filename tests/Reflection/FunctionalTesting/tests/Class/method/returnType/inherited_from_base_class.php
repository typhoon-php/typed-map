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
                 * @return non-empty-string
                 */
                public function a(): string {}
            }
            
            class B extends A
            {
            }
            PHP,
    )[classId('B')]->method('a') ?? throw new \LogicException();

    assertEquals(types::string, $reflection->returnType(Kind::Native));
    assertEquals(types::nonEmptyString, $reflection->returnType(Kind::Annotated));
    assertEquals(types::nonEmptyString, $reflection->returnType(Kind::Resolved));
};
