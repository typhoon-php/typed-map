<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Reflection\Locator\Resource;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

return static function (TyphoonReflector $reflector): void {
    $properties = $reflector
        ->withResource(Resource::fromCode(
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
    assertFalse($notReadonly->isReadonly(DeclarationKind::Native));
    assertFalse($notReadonly->isReadonly(DeclarationKind::Annotated));
    assertFalse($notReadonly->isReadonly());

    $nativeReadonly = $properties['nativeReadonly'];
    assertTrue($nativeReadonly->isReadonly(DeclarationKind::Native));
    assertFalse($nativeReadonly->isReadonly(DeclarationKind::Annotated));
    assertTrue($nativeReadonly->isReadonly());

    $phpDocReadonly = $properties['phpDocReadonly'];
    assertFalse($phpDocReadonly->isReadonly(DeclarationKind::Native));
    assertTrue($phpDocReadonly->isReadonly(DeclarationKind::Annotated));
    assertTrue($phpDocReadonly->isReadonly());

    $nativeAndPhpDocReadonly = $properties['nativeAndPhpDocReadonly'];
    assertTrue($nativeAndPhpDocReadonly->isReadonly(DeclarationKind::Native));
    assertTrue($nativeAndPhpDocReadonly->isReadonly(DeclarationKind::Annotated));
    assertTrue($nativeAndPhpDocReadonly->isReadonly());
};
