<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use Typhoon\TypeContext\Internal\ConstantImportTable;
use Typhoon\TypeContext\Internal\FunctionImportTable;
use Typhoon\TypeContext\Internal\MainImportTable;

/**
 * @api
 * @readonly
 */
final class RelativeName extends Name
{
    /**
     * @param non-empty-list<UnqualifiedName> $segments
     */
    public function __construct(
        public readonly array $segments,
    ) {}

    public static function fromString(string $name): self
    {
        $name = parent::fromString($name);

        if (!$name instanceof self) {
            throw new InvalidName();
        }

        return $name;
    }

    public function toString(): string
    {
        return self::RELATIVE_PREFIX . self::DELIMITER . self::segmentsToString($this->segments);
    }

    public function toFullyQualified(): FullyQualifiedName
    {
        return new FullyQualifiedName($this->segments);
    }

    public function firstSegment(): UnqualifiedName
    {
        return $this->segments[0];
    }

    public function lastSegment(): UnqualifiedName
    {
        return $this->segments[\count($this->segments) - 1];
    }

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsClassName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
    ): FullyQualifiedName {
        if ($namespace === null) {
            return new FullyQualifiedName($this->segments);
        }

        return new FullyQualifiedName([...$namespace->segments, ...$this->segments]);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsFunctionName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
        FunctionImportTable $functionImportTable,
        callable $functionExists,
    ): FullyQualifiedName {
        return $this->resolveAsClassName($namespace, $mainImportTable);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsConstantName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
        ConstantImportTable $constantImportTable,
        callable $constantExists,
    ): FullyQualifiedName {
        return $this->resolveAsClassName($namespace, $mainImportTable);
    }
}
