<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name as NameNode;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;

/**
 * @api
 * @readonly
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
     * @return ($name is null ? null : (
     *     $name is Identifier ? UnqualifiedName : (
     *         $name is FullyQualified ? FullyQualifiedName : (
     *             $name is Relative ? RelativeName : UnqualifiedName|QualifiedName
     *         )
     *     )
     * ))
     */
    final public static function fromNode(null|Identifier|NameNode $name): ?self
    {
        if ($name === null) {
            return null;
        }

        if ($name instanceof Identifier) {
            return UnqualifiedName::fromString($name->name);
        }

        /** @var NameNode $name */
        $parts = $name->getParts();
        \assert($parts !== [] && array_is_list($parts));
        $segments = array_map(UnqualifiedName::fromString(...), $parts);

        if ($name instanceof FullyQualified) {
            return new FullyQualifiedName($segments);
        }

        if ($name instanceof Relative) {
            return new RelativeName($segments);
        }

        if (\count($segments) === 1) {
            return $segments[0];
        }

        return new QualifiedName($segments);
    }

    /**
     * @param non-empty-list<UnqualifiedName> $segments
     * @return non-empty-string
     */
    final protected static function segmentsToString(array $segments): string
    {
        return implode(self::DELIMITER, array_map(
            static fn(UnqualifiedName $segment): string => $segment->toString(),
            $segments,
        ));
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

    abstract public function lastSegment(): UnqualifiedName;
}
