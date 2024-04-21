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
            return self::fromSegments(FullyQualifiedName::class, \array_slice($segments, 1));
        }

        if (strtolower($segments[0]) === self::RELATIVE_PREFIX) {
            return self::fromSegments(RelativeName::class, \array_slice($segments, 1));
        }

        if (\count($segments) === 1) {
            return UnqualifiedName::fromString($segments[0]);
        }

        return self::fromSegments(QualifiedName::class, $segments);
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
            return UnqualifiedName::fromIdentifier($name);
        }

        /** @var NameNode $name */
        $segments = array_map(UnqualifiedName::fromString(...), $name->getParts());

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
     * @param FullyQualifiedName::class|RelativeName::class|QualifiedName::class $class
     * @param list<string> $segments
     */
    private static function fromSegments(string $class, array $segments): self
    {
        if (\count($segments) === 0) {
            throw new InvalidName('Empty name');
        }

        return new $class(array_map(UnqualifiedName::fromString(...), $segments));
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
