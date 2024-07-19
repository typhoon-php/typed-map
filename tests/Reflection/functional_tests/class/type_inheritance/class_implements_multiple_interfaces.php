<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Type\types;
use function PHPUnit\Framework\assertEquals;

return static function (TyphoonReflector $reflector): void {
    $method = $reflector
        ->withResource(new Resource(
            <<<'PHP'
                <?php
                interface I1
                {
                    public function a(): string;
                }
                interface I2
                {
                    /** @return non-empty-string */
                    public function a(): string;
                }
                class B implements I1, I2
                {
                    public function a(): string { return '1'; }
                }
                PHP,
        ))
        ->reflectClass('B')
        ->methods()['a'];

    assertEquals(types::string, $method->returnType(DeclarationKind::Native));
    assertEquals(types::nonEmptyString, $method->returnType(DeclarationKind::Annotated));
    assertEquals(types::nonEmptyString, $method->returnType());
};
