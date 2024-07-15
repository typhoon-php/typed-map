<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\CompleteReflection;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\Reflection\ClassKind;
use Typhoon\Reflection\Internal\Data\Data;
use Typhoon\Reflection\Internal\Data\TypeData;
use Typhoon\Reflection\Internal\Data\Visibility;
use Typhoon\Reflection\Internal\ReflectionHook\ClassReflectionHook;
use Typhoon\Reflection\Internal\Reflector;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Type\types;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 */
final class CompleteEnumReflection implements ClassReflectionHook
{
    public function process(NamedClassId|AnonymousClassId $id, TypedMap $data, Reflector $reflector): TypedMap
    {
        if ($data[Data::ClassKind] !== ClassKind::Enum) {
            return $data;
        }

        $backingType = $data[Data::EnumBackingType];
        $interfaces = $data[Data::UnresolvedInterfaces];
        $properties = $data[Data::Properties];
        $methods = $data[Data::Methods];
        $staticType = types::static($id);

        $interfaces[\UnitEnum::class] = [];

        $properties['name'] = (new TypedMap())
            ->set(Data::NativeReadonly, true)
            ->set(Data::Type, new TypeData(types::string))
            ->set(Data::Visibility, Visibility::Public);

        $methods['cases'] = (new TypedMap())
            ->set(Data::Static, true)
            ->set(Data::Type, new TypeData(types::array, types::list($staticType)))
            ->set(Data::Visibility, Visibility::Public)
            ->set(Data::InternallyDefined, true);

        if ($backingType !== null) {
            $interfaces[\BackedEnum::class] = [];

            $properties['value'] = (new TypedMap())
                ->set(Data::NativeReadonly, true)
                ->set(Data::Type, new TypeData($backingType))
                ->set(Data::Visibility, Visibility::Public);

            $methods['from'] = $methods['cases']
                ->set(Data::Type, new TypeData($staticType))
                ->set(Data::Parameters, [
                    'value' => (new TypedMap())->set(Data::Type, new TypeData(types::arrayKey, $backingType)),
                ]);

            $methods['tryFrom'] = $methods['from']
                ->set(Data::Type, new TypeData(types::nullable($staticType)));
        }

        return $data
            ->set(Data::UnresolvedInterfaces, $interfaces)
            ->set(Data::Properties, $properties)
            ->set(Data::Methods, $methods);
    }
}
