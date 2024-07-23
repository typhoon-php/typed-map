<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Reflection\Locator\Resource;
use function PHPUnit\Framework\assertSame;

return static function (TyphoonReflector $reflector): void {
    $parameters = $reflector
        ->withResource(Resource::fromCode(
            <<<'PHP'
                <?php
                /** 
                 * @method m($f=__FILE__, $d=__DIR__, $l=__LINE__, $c=__CLASS__, $m=__METHOD__, $func=__FUNCTION__, $str="\"\n")
                 */
                final class A {}
                PHP,
            (new TypedMap())->with(Data::File, 'dir/file.php'),
        ))
        ->reflectClass('A')
        ->methods()['m']
        ->parameters();

    assertSame($parameters['f']->defaultValue(), 'dir/file.php');
    assertSame($parameters['d']->defaultValue(), 'dir');
    assertSame($parameters['l']->defaultValue(), 3);
    assertSame($parameters['c']->defaultValue(), 'A');
    assertSame($parameters['m']->defaultValue(), 'A::m');
    assertSame($parameters['func']->defaultValue(), 'm');
    assertSame($parameters['str']->defaultValue(), "\"\n");
};
