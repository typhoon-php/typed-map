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
    private const TENTATIVE_TYPE_ATTRIBUTE = 'JetBrains\PhpStorm\Internal\TentativeType';

    public function process(NamedClassId|AnonymousClassId $id, TypedMap $data, Reflector $reflector): TypedMap
    {
        return $data->set(Data::Methods, array_map(
            static function (TypedMap $method): TypedMap {
                $type = $method[Data::Type];

                if ($type->native === null || !self::hasTentativeAttribute($method[Data::Attributes] ?? [])) {
                    return $method;
                }

                return $method->set(Data::Type, $type->withTentative($type->native)->withNative(null));
            },
            $data[Data::Methods],
        ));
    }

    /**
     * @param list<TypedMap> $attributes
     */
    private static function hasTentativeAttribute(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute[Data::AttributeClassName] === self::TENTATIVE_TYPE_ATTRIBUTE) {
                return true;
            }
        }

        return false;
    }
}
