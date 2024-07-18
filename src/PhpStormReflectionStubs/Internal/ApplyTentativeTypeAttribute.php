<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs\Internal;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\Reflection\Internal\Data\Data;
use Typhoon\Reflection\Internal\ReflectionHook\ClassReflectionHook;
use Typhoon\Reflection\Internal\Reflector;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\PhpStormReflectionStubs
 */
final class ApplyTentativeTypeAttribute implements ClassReflectionHook
{
    private const ATTRIBUTE = 'JetBrains\PhpStorm\Internal\TentativeType';

    public function process(NamedClassId|AnonymousClassId $id, TypedMap $data, Reflector $reflector): TypedMap
    {
        return $data->with(Data::Methods, array_map(
            static function (TypedMap $method): TypedMap {
                $type = $method[Data::Type];

                if ($type->native === null || !self::hasTentativeAttribute($method[Data::Attributes])) {
                    return $method;
                }

                return $method->with(Data::Type, $type->withTentative($type->native)->withNative(null));
            },
            $data[Data::Methods],
        ));
    }

    /**
     * @param list<TypedMap> $attributes
     */
    private static function hasTentativeAttribute(array $attributes): bool
    {
        return array_any(
            $attributes,
            static fn(TypedMap $attribute): bool => $attribute[Data::AttributeClassName] === self::ATTRIBUTE,
        );
    }
}
