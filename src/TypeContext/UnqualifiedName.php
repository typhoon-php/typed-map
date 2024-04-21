<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\Node\Identifier;

/**
 * @api
 * @readonly
 */
final class UnqualifiedName extends Name
{
    /**
     * @param non-empty-string $name
     */
    private function __construct(
        private readonly string $name,
    ) {}

    public static function fromString(string $name): self
    {
        if ($name === '' || str_contains($name, self::DELIMITER)) {
            throw new InvalidName(sprintf('"%s" is not a valid unqualified name', $name));
        }

        return new self($name);
    }

    public static function fromIdentifier(Identifier $node): self
    {
        return new self($node->name);
    }

    public static function self(): self
    {
        return new self(self::SELF);
    }

    public static function parent(): self
    {
        return new self(self::PARENT);
    }

    public static function static(): self
    {
        return new self(self::STATIC);
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function toFullyQualified(): FullyQualifiedName
    {
        return new FullyQualifiedName([$this]);
    }

    public function lastSegment(): self
    {
        return $this;
    }

    /**
     * @return non-empty-lowercase-string
     */
    public function toLowerCaseString(): string
    {
        return strtolower($this->name);
    }

    public function isConstantLike(): bool
    {
        return preg_match('/[a-z]/', $this->name) === 0;
    }

    public function isClassRelativeName(): bool
    {
        return \in_array($this->toLowerCaseString(), [self::SELF, self::PARENT, self::STATIC], true);
    }
}
