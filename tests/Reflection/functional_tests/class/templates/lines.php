<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use function PHPUnit\Framework\assertSame;

return static function (TyphoonReflector $reflector): void {
    $templates = $reflector
        ->withResource(new Resource(<<<'PHP'
            <?php
            /** 
             * @template TSingleLine
             * @template TMultiLine Description Line 1
             *                      Description Line 2
             */
            class A {}
            PHP))
        ->reflectClass('A')
        ->templates();

    assertSame(3, $templates['TSingleLine']->startLine());
    assertSame(3, $templates['TSingleLine']->endLine());
    assertSame(4, $templates['TMultiLine']->startLine());
    assertSame(5, $templates['TMultiLine']->endLine());
};
