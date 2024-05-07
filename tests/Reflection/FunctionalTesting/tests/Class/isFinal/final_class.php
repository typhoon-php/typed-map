<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use function Typhoon\DeclarationId\classId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php final class A {}')[classId('A')];

    assertTrue($reflection->isFinal(Kind::Native));
    assertFalse($reflection->isFinal(Kind::Annotated));
    assertTrue($reflection->isFinal());
};
