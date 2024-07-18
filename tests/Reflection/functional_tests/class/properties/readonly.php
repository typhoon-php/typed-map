<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return static function (TyphoonReflector $reflector): void {
    $properties = $reflector
        ->withResource(new Resource(
            <<<'PHP'
                <?php
                final class A
                {
                    public string $notReadonly;
                    
                    public readonly string $nativeReadonly;
                    
                    /** @readonly */
                    public string $phpDocReadonly;
                    
                    /** @readonly */
                    public readonly string $nativeAndPhpDocReadonly;
                }
                PHP,
        ))
        ->reflectClass('A')
        ->properties();

    $notReadonly = $properties['notReadonly'];
    assertFalse($notReadonly->isReadonly(Kind::Native));
    assertFalse($notReadonly->isReadonly(Kind::Annotated));
    assertFalse($notReadonly->isReadonly());

    $nativeReadonly = $properties['nativeReadonly'];
    assertTrue($nativeReadonly->isReadonly(Kind::Native));
    assertFalse($nativeReadonly->isReadonly(Kind::Annotated));
    assertTrue($nativeReadonly->isReadonly());

    $phpDocReadonly = $properties['phpDocReadonly'];
    assertFalse($phpDocReadonly->isReadonly(Kind::Native));
    assertTrue($phpDocReadonly->isReadonly(Kind::Annotated));
    assertTrue($phpDocReadonly->isReadonly());

    $nativeAndPhpDocReadonly = $properties['nativeAndPhpDocReadonly'];
    assertTrue($nativeAndPhpDocReadonly->isReadonly(Kind::Native));
    assertTrue($nativeAndPhpDocReadonly->isReadonly(Kind::Annotated));
    assertTrue($nativeAndPhpDocReadonly->isReadonly());
};
