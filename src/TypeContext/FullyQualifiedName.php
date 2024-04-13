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
final class FullyQualifiedName extends Name
{
    /**
     * @param non-empty-list<UnqualifiedName> $segments
     */
    public function __construct(
        public readonly array $segments,
    ) {}

    public static function fromString(string $name): self
    {
        return parent::fromString($name)->toFullyQualified();
    }

    public function toString(): string
    {
        return self::DELIMITER . $this->toStringWithoutSlash();
    }

    public function toFullyQualified(): self
    {
        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function toStringWithoutSlash(): string
    {
        return self::segmentsToString($this->segments);
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
        ?self $namespace,
        MainImportTable $mainImportTable,
    ): self {
        return $this;
    }

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsFunctionName(
        ?self $namespace,
        MainImportTable $mainImportTable,
        FunctionImportTable $functionImportTable,
        callable $functionExists,
    ): self {
        return $this;
    }

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsConstantName(
        ?self $namespace,
        MainImportTable $mainImportTable,
        ConstantImportTable $constantImportTable,
        callable $constantExists,
    ): self {
        return $this;
    }
}
