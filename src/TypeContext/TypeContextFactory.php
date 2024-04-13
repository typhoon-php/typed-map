<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

/**
 * @api
 * @readonly
 * @psalm-import-type Exists from TypeContext
 */
final class TypeContextFactory
{
    /**
     * @var Exists
     */
    private readonly mixed $classExists;

    /**
     * @var Exists
     */
    private readonly mixed $functionExists;

    /**
     * @var Exists
     */
    private readonly mixed $constantExists;

    /**
     * @param ?Exists $classExists
     * @param ?Exists $functionExists
     * @param ?Exists $constantExists
     */
    public function __construct(
        ?callable $classExists = null,
        ?callable $functionExists = null,
        ?callable $constantExists = null,
    ) {
        $this->classExists = $classExists ?? static fn(string $class): bool => class_exists($class) || interface_exists($class);
        $this->functionExists = $functionExists ?? 'function_exists';
        $this->constantExists = $constantExists ?? 'defined';
    }

    public function start(?Name $namespace = null): TypeContext
    {
        return new TypeContext(
            namespace: $namespace?->toFullyQualified(),
            classExists: $this->classExists,
            functionExists: $this->functionExists,
            constantExists: $this->constantExists,
        );
    }
}
