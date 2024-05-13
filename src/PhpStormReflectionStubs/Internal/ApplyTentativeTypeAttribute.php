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

    public function reflect(FunctionId|ClassId|AnonymousClassId $id, TypedMap $data): TypedMap
    {
        return $data->modify(Data::Methods, fn(array $methods): array => array_map(
            function (TypedMap $method): TypedMap {
                $type = $method[Data::Type];

                if ($type->native === null || !$this->hasTentativeAttribute($method[Data::Attributes] ?? [])) {
                    return $method;
                }

                return $method->set(Data::Type, $type->withTentative($type->native)->withNative(null));
            },
            $methods,
        ));
    }

    /**
     * @param list<TypedMap> $attributes
     */
    private function hasTentativeAttribute(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute[Data::AttributeClass] === self::TENTATIVE_TYPE_ATTRIBUTE) {
                return true;
            }
        }

        return false;
    }
}
