<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use Typhoon\TypeContext\Internal\ConstantImportTable;
use Typhoon\TypeContext\Internal\FunctionImportTable;
use Typhoon\TypeContext\Internal\MainImportTable;

/**
 * @api
 * @readonly
 * @psalm-import-type Exists from TypeContext
 */
abstract class Name
{
    final public const SELF = 'self';
    final public const PARENT = 'parent';
    final public const STATIC = 'static';
    final public const DELIMITER = '\\';
    final public const RELATIVE_PREFIX = 'namespace';

    public static function fromString(string $name): self
    {
        $segments = explode(self::DELIMITER, $name);

        if ($segments[0] === '') {
            return new FullyQualifiedName(self::parseSegments(\array_slice($segments, 1)));
        }

        if (strtolower($segments[0]) === self::RELATIVE_PREFIX) {
            return new RelativeName(self::parseSegments(\array_slice($segments, 1)));
        }

        if (\count($segments) === 1) {
            return UnqualifiedName::fromString($segments[0]);
        }

        return new QualifiedName(self::parseSegments($segments));
    }

    /**
     * @param list<string> $segments
     * @return non-empty-list<UnqualifiedName>
     */
    private static function parseSegments(array $segments): array
    {
        if (\count($segments) === 0) {
            throw new InvalidName('Empty name');
        }

        return array_map(UnqualifiedName::fromString(...), $segments);
    }

    /**
     * @return non-empty-string
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return non-empty-string
     */
    abstract public function toString(): string;

    abstract public function toFullyQualified(): FullyQualifiedName;

    abstract public function firstSegment(): UnqualifiedName;

    abstract public function lastSegment(): UnqualifiedName;

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    abstract public function resolveAsClassName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
    ): FullyQualifiedName;

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     * @param Exists $functionExists
     */
    abstract public function resolveAsFunctionName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
        FunctionImportTable $functionImportTable,
        callable $functionExists,
    ): FullyQualifiedName;

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     * @param Exists $constantExists
     */
    abstract public function resolveAsConstantName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
        ConstantImportTable $constantImportTable,
        callable $constantExists,
    ): FullyQualifiedName;

    /**
     * @param non-empty-list<UnqualifiedName> $segments
     * @return non-empty-string
     */
    final protected function segmentsToString(array $segments): string
    {
        return implode(self::DELIMITER, array_map(
            static fn(UnqualifiedName $segment): string => $segment->toString(),
            $segments,
        ));
    }
}
