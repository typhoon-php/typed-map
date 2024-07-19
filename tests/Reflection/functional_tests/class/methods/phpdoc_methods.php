<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Type\types;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

return static function (TyphoonReflector $reflector): void {
    $methods = $reflector
        ->withResource(new Resource(
            <<<'PHP'
                <?php
                /** 
                 * @method void method1(string $a)
                 * @method static positive-int method2(&$byRef, float ...$variadic)
                 */
                final class A {}
                PHP,
        ))
        ->reflectClass('A')
        ->methods();

    $method1 = $methods['method1'];
    assertTrue($method1->isAnnotated());
    assertFalse($method1->isNative());
    assertFalse($method1->isStatic());
    assertNull($method1->returnType(DeclarationKind::Native));
    assertSame(types::void, $method1->returnType(DeclarationKind::Annotated));
    assertSame(types::void, $method1->returnType());

    $method1ParamA = $method1->parameters()['a'];
    assertFalse($method1ParamA->isVariadic());
    assertFalse($method1ParamA->isPassedByReference());
    assertNull($method1ParamA->type(DeclarationKind::Native));
    assertSame(types::string, $method1ParamA->type(DeclarationKind::Annotated));
    assertSame(types::string, $method1ParamA->type());

    $method2 = $methods['method2'];
    assertTrue($method2->isAnnotated());
    assertFalse($method2->isNative());
    assertTrue($method2->isStatic());
    assertNull($method2->returnType(DeclarationKind::Native));
    assertSame(types::positiveInt, $method2->returnType(DeclarationKind::Annotated));
    assertSame(types::positiveInt, $method2->returnType());

    $method2ParamByRef = $method2->parameters()['byRef'];
    assertFalse($method2ParamByRef->isVariadic());
    assertTrue($method2ParamByRef->isPassedByReference());
    assertNull($method2ParamByRef->type(DeclarationKind::Native));
    assertNull($method2ParamByRef->type(DeclarationKind::Annotated));
    assertSame(types::mixed, $method2ParamByRef->type());

    $method2ParamVariadic = $method2->parameters()['variadic'];
    assertTrue($method2ParamVariadic->isVariadic());
    assertFalse($method2ParamVariadic->isPassedByReference());
    assertNull($method2ParamVariadic->type(DeclarationKind::Native));
    assertSame(types::float, $method2ParamVariadic->type(DeclarationKind::Annotated));
    assertSame(types::float, $method2ParamVariadic->type());
};
