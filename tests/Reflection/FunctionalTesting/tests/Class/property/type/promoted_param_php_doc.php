<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\types;
use function PHPUnit\Framework\assertEquals;
use function Typhoon\DeclarationId\namedClassId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            class A
            {
                /** @param non-empty-string $a */
                public function __construct(
                    public string $a,
                ) {}
            }
            PHP,
    )[namedClassId('A')]->properties['a'];

    assertEquals(types::string, $reflection->type(Kind::Native));
    assertEquals(types::nonEmptyString, $reflection->type(Kind::Annotated));
    assertEquals(types::nonEmptyString, $reflection->type(Kind::Resolved));
};
