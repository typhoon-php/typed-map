<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

/**
 * @api
 * @readonly
 */
final class QualifiedName extends Name
{
    /**
     * @param non-empty-list<UnqualifiedName> $segments
     */
    public function __construct(
        public readonly array $segments,
    ) {
        if (\count($segments) < 2) {
            throw new InvalidName(sprintf('Qualified name expects at least 2 segments, got %d', \count($segments)));
        }
    }

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
        return self::segmentsToString($this->segments);
    }

    public function toFullyQualified(): FullyQualifiedName
    {
        return new FullyQualifiedName($this->segments);
    }

    public function lastSegment(): UnqualifiedName
    {
        return $this->segments[\count($this->segments) - 1];
    }
}
