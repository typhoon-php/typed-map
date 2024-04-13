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
        if (preg_match('/^[a-zA-Z_\x80-\xff][\w\x80-\xff]*$/', $name) !== 1) {
            throw new InvalidName(sprintf('"%s" is not a valid PHP label', $name));
        }

        \assert($name !== '');

        return new self($name);
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

    public function firstSegment(): self
    {
        return $this;
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

    /**
     * @internal
     * @psalm-internal Typhoon\TypeContext
     */
    public function resolveAsClassName(
        ?FullyQualifiedName $namespace,
        MainImportTable $mainImportTable,
    ): FullyQualifiedName {
        $imported = $mainImportTable->getName($this);

        if ($imported !== null) {
            return $imported;
        }

        if (\in_array($this->toLowerCaseString(), [self::SELF, self::PARENT, self::STATIC], true)) {
            throw new InvalidName(sprintf('Cannot resolve %s', $this->name));
        }

        if ($namespace === null) {
            return new FullyQualifiedName([$this]);
        }

        return new FullyQualifiedName([...$namespace->segments, $this]);
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
        $imported = $functionImportTable->getName($this);

        if ($imported !== null) {
            return $imported;
        }

        if ($namespace === null) {
            return new FullyQualifiedName([$this]);
        }

        $namespacedName = new FullyQualifiedName([...$namespace->segments, $this]);

        if (($functionExists)($namespacedName->toString())) {
            return $namespacedName;
        }

        return new FullyQualifiedName([$this]);
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
        $imported = $constantImportTable->getName($this);

        if ($imported !== null) {
            return $imported;
        }

        if ($namespace === null) {
            return new FullyQualifiedName([$this]);
        }

        $namespacedName = new FullyQualifiedName([...$namespace->segments, $this]);

        if (($constantExists)($namespacedName->toString())) {
            return $namespacedName;
        }

        return new FullyQualifiedName([$this]);
    }
}
