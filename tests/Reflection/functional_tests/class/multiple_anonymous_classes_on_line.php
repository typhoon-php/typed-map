<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use PHPUnit\Framework\TestCase;
use Typhoon\ChangeDetector\InMemoryChangeDetector;
use Typhoon\Reflection\Internal\Data\Data;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;

return static function (TyphoonReflector $reflector, TestCase $test): void {
    $reflector = $reflector->withResource(new Resource(
        '<?php new class {}; new class {};',
        (new TypedMap())
            ->with(Data::File, 'some.php')
            ->with(Data::UnresolvedChangeDetectors, [new InMemoryChangeDetector()]),
    ));

    $test->expectExceptionMessage('because 2 anonymous classes are declared at columns 11, 25');

    $reflector->reflectAnonymousClass('some.php', 1);
};
