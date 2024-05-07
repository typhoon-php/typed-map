<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;
use function Typhoon\DeclarationId\classId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php enum A: string {}')[classId('A')];

    assertFalse($reflection->isInstanceOf(\Iterator::class));
    assertFalse($reflection->isInstanceOf(\stdClass::class));
    assertTrue($reflection->isInstanceOf('A'));
    assertTrue($reflection->isInstanceOf(\UnitEnum::class));
    assertTrue($reflection->isInstanceOf(\BackedEnum::class));
};
