<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs\Internal;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\ReflectionHook;
use Typhoon\TypedMap\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\PhpStormReflectionStubs
 */
final class ApplyTentativeTypeAttribute implements ReflectionHook
{
    private const TENTATIVE_TYPE_ATTRIBUTE = 'JetBrains\\PhpStorm\\Internal\\TentativeType';

    /**
     * @param list<TypedMap> $attributes
     */
    private static function hasTentativeTypeAttribute(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if (($attribute[Data::AttributeClass()] ?? null) === self::TENTATIVE_TYPE_ATTRIBUTE) {
                return true;
            }
        }

        return false;
    }

    public function reflect(FunctionId|ClassId|AnonymousClassId $id, TypedMap $data): TypedMap
    {
        if (!$id instanceof ClassId || !isset($data[Data::Methods()])) {
            return $data;
        }

        return $data->with(Data::Methods(), array_map(
            static function (TypedMap $method): TypedMap {
                $nativeType = $method[Data::NativeType()] ?? null;

                if ($nativeType === null || !self::hasTentativeTypeAttribute($method[Data::Attributes()] ?? [])) {
                    return $method;
                }

                return $method
                    ->with(Data::TentativeType(), $nativeType)
                    ->without(Data::NativeType());
            },
            $data[Data::Methods()],
        ));
    }
}
