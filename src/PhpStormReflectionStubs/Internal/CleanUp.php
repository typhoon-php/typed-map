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
final class CleanUp implements ReflectionHook
{
    private const ATTRIBUTE_PREFIX = 'JetBrains\\';

    public function reflect(FunctionId|ClassId|AnonymousClassId $id, TypedMap $data): TypedMap
    {
        $data = $this->cleanUp($data);

        if ($id instanceof FunctionId) {
            return $data;
        }

        if ($id->name === \Traversable::class) {
            $data = $data->with(Data::UnresolvedInterfaces(), []);
        }

        if (isset($data[Data::ClassConstants()])) {
            $data = $data->with(Data::ClassConstants(), array_map($this->cleanUp(...), $data[Data::ClassConstants()]));
        }

        if (isset($data[Data::Properties()])) {
            $data = $data->with(Data::Properties(), array_map($this->cleanUp(...), $data[Data::Properties()]));
        }

        if (isset($data[Data::Methods()])) {
            $data = $data->with(Data::Methods(), array_map($this->cleanUp(...), $data[Data::Methods()]));
        }

        return $data;
    }

    private function cleanUp(TypedMap $data): TypedMap
    {
        return $data
            ->without(Data::StartLine(), Data::EndLine(), Data::PhpDoc())
            ->with(Data::Attributes(), array_values(array_filter(
                $data[Data::Attributes()],
                static fn(TypedMap $attribute): bool => !str_starts_with($attribute[Data::AttributeClass()], self::ATTRIBUTE_PREFIX),
            )));
    }
}