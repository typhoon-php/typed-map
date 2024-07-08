<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\Kind;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php enum A: string {}')[Id::class('A')];

    assertTrue($reflection->isFinal(Kind::Native));
    assertFalse($reflection->isFinal(Kind::Annotated));
    assertTrue($reflection->isFinal());
};
