<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs\Internal;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\DataReflector;
use Typhoon\Reflection\Internal\ReflectionHook;
use Typhoon\TypedMap\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\PhpStormReflectionStubs
 */
final class CleanUp implements ReflectionHook
{
    private const ATTRIBUTE_PREFIX = 'JetBrains\\';

    public function reflect(FunctionId|NamedClassId|AnonymousClassId $id, TypedMap $data, DataReflector $reflector): TypedMap
    {
        if ($id->name === \Traversable::class) {
            $data = $data->unset(Data::UnresolvedInterfaces);
        }

        return $this->cleanUp($data)
            ->modifyIfSet(Data::ClassConstants, fn(array $constants): array => array_map($this->cleanUp(...), $constants))
            ->modifyIfSet(Data::Properties, fn(array $properties): array => array_map($this->cleanUp(...), $properties))
            ->modifyIfSet(Data::Methods, fn(array $methods): array => array_map($this->cleanUp(...), $methods));
    }

    private function cleanUp(TypedMap $data): TypedMap
    {
        return $data
            ->unset(Data::StartLine, Data::EndLine, Data::PhpDoc)
            ->modifyIfSet(Data::Attributes, static fn(array $attributes): array => array_values(array_filter(
                $attributes,
                static fn(TypedMap $attribute): bool => !str_starts_with($attribute[Data::AttributeClassName], self::ATTRIBUTE_PREFIX),
            )));
    }
}
