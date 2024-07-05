<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\CompleteReflection;

use Typhoon\ChangeDetector\ChangeDetectors;
use Typhoon\ChangeDetector\InMemoryChangeDetector;
use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\ReflectionHook;
use Typhoon\TypedMap\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 */
final class ResolveChangeDetector implements ReflectionHook
{
    public function reflect(FunctionId|ClassId $id, TypedMap $data): TypedMap
    {
        $unresolved = $data[Data::UnresolvedChangeDetectors];

        return $data->set(Data::ChangeDetector, $unresolved === [] ? new InMemoryChangeDetector() : ChangeDetectors::from($unresolved));
    }
}