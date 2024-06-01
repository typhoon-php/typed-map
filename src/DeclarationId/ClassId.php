<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
abstract class ClassId extends DeclarationId
{
    /**
     * @var non-empty-string
     */
    public readonly string $name;

    /**
     * @param non-empty-string $name
     */
    protected function __construct(string $name)
    {
        $this->name = $name;
    }

    final public function reflect(): \ReflectionClass
    {
        return new \ReflectionClass($this->name);
    }
}
