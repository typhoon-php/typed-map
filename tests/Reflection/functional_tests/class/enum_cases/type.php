<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Type\types;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector
        ->withResource(new Resource('<?php enum A { case X; }'))
        ->reflectClass('A')
        ->enumCases()['X'];

    assertNull($reflection->type(DeclarationKind::Native));
    assertEquals(types::classConstant('A', 'X'), $reflection->type(DeclarationKind::Annotated));
    assertEquals(types::classConstant('A', 'X'), $reflection->type());
};
