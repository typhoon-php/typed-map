<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertTrue;
use function Typhoon\DeclarationId\classId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode('<?php abstract class A {}')[classId('A')];

    assertTrue($reflection->isAbstract());
};