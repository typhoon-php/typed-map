<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php class A {}')[Id::namedClass('A')];

    assertFalse($reflection->isAbstractClass());
};
