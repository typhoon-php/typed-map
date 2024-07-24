<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs\Internal;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\AnonymousFunctionId;
use Typhoon\DeclarationId\ConstantId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\DeclarationId\NamedFunctionId;
use Typhoon\Reflection\Internal\ClassHook;
use Typhoon\Reflection\Internal\ConstantHook;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\FunctionHook;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Reflection\TyphoonReflector;

/**
 * @internal
 * @psalm-internal Typhoon\PhpStormReflectionStubs
 */
enum CleanUp implements ConstantHook, FunctionHook, ClassHook
{
    case Instance;
    private const ATTRIBUTE_PREFIX = 'JetBrains\\';

    public function process(ConstantId|NamedFunctionId|AnonymousFunctionId|NamedClassId|AnonymousClassId $id, TypedMap $data, TyphoonReflector $reflector): TypedMap
    {
        // https://github.com/JetBrains/phpstorm-stubs/pull/1528
        if ($id instanceof NamedClassId && $id->name === \Traversable::class) {
            $data = $data->without(Data::UnresolvedInterfaces);
        }

        // todo issue
        if ($id instanceof NamedClassId && $id->name === \Throwable::class) {
            $methods = $data[Data::Methods];
            unset($methods['__toString']);
            $data = $data->with(Data::Methods, $methods);
        }

        return self::cleanUp($data)
            ->with(Data::Constants, array_map(self::cleanUp(...), $data[Data::Constants]))
            ->with(Data::Properties, array_map(self::cleanUp(...), $data[Data::Properties]))
            ->with(Data::Methods, array_map(self::cleanUp(...), $data[Data::Methods]));
    }

    private static function cleanUp(TypedMap $data): TypedMap
    {
        return $data
            ->with(Data::Parameters, array_map(self::cleanUp(...), $data[Data::Parameters]))
            ->with(Data::Attributes, array_values(array_filter(
                $data[Data::Attributes],
                static fn(TypedMap $attribute): bool => !str_starts_with($attribute[Data::AttributeClassName], self::ATTRIBUTE_PREFIX),
            )));
    }
}
