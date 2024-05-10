<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function Typhoon\DeclarationId\anyClassId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php interface A {}')[anyClassId('A')];

    assertFalse($reflection->isFinal(Kind::Native));
    assertFalse($reflection->isFinal(Kind::Annotated));
    assertFalse($reflection->isFinal());
};
