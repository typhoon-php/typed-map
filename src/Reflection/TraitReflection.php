<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\DeclarationId\NamedClassId;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;

/**
 * @api
 * @readonly
 * @template-covariant TObject of object
 * @extends ClassLikeReflection<TObject, NamedClassId>
 */
final class TraitReflection extends ClassLikeReflection
{
    /**
     * @var class-string<TObject>
     */
    public readonly string $name;

    public function __construct(NamedClassId $id, TypedMap $data, Reflector $reflector)
    {
        /** @var class-string<TObject> */
        $this->name = $id->name;
        parent::__construct($id, $data, $reflector);
    }
}