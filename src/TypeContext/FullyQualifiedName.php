<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

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

    public function lastSegment(): UnqualifiedName
    {
        return $this->segments[\count($this->segments) - 1];
    }
}
