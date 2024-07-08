<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php interface A {}')[Id::class('A')];

    assertFalse($reflection->isFinal(Kind::Native));
    assertFalse($reflection->isFinal(Kind::Annotated));
    assertFalse($reflection->isFinal());
};
