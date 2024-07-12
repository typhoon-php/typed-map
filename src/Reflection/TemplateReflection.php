<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\DeclarationId\TemplateId;
use Typhoon\Reflection\Internal\Data\Data;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Type\Type;
use Typhoon\Type\Variance;

/**
 * @api
 * @extends Reflection<TemplateId>
 */
final class TemplateReflection extends Reflection
{
    /**
     * @var non-empty-string
     */
    public readonly string $name;

    /**
     * @var non-negative-int
     */
    public readonly int $index;

    public function __construct(TemplateId $id, TypedMap $data)
    {
        $this->name = $id->name;
        $this->index = $data[Data::Index];

        parent::__construct($id, $data);
    }

    public function variance(): Variance
    {
        return $this->data[Data::Variance];
    }

    public function constraint(): Type
    {
        return $this->data[Data::Constraint];
    }
}
