<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\TyphoonReflector;
use function PHPUnit\Framework\assertSame;
use function Typhoon\DeclarationId\anonymousClassId;

return static function (TyphoonReflector $reflector): void {
    TestCase::markTestSkipped();

    new class () {};

    $reflection = $reflector->reflect(anonymousClassId(__FILE__, 13));

    assertSame(sprintf("class@anonymous\x00%s:%d", __FILE__, 13), $reflection->name);
};