<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

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

    public function lastSegment(): UnqualifiedName
    {
        return $this->segments[\count($this->segments) - 1];
    }
}
