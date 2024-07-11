<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AnonymousClassId extends Id
{
    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?positive-int $column
     * @param ?class-string $name
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly ?int $column = null,
        public readonly ?string $name = null,
    ) {}

    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    private static function normalizeClassNameForException(string $name): string
    {
        /** @var non-empty-string */
        return str_replace("\x00", '\0', $name);
    }

    /**
     * @param non-empty-string $name
     */
    protected static function fromName(string $name): self
    {
        if (preg_match('/@anonymous\x00(.+):(\d+)/', $name, $matches) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid anonymous class name "%s"', self::normalizeClassNameForException($name)));
        }

        /** @var non-empty-string $file */
        $file = $matches[1];
        $line = (int) $matches[2];

        if ($line <= 0) {
            throw new \InvalidArgumentException(sprintf('Invalid anonymous class name "%s"', self::normalizeClassNameForException($name)));
        }

        return new self(
            file: $file,
            line: $line,
            name: class_exists($name, autoload: false) ? $name : null,
        );
    }

    protected static function doFromReflection(\ReflectionClass $reflection): self
    {
        $file = $reflection->getFileName();
        \assert($file !== false, 'Anonymous class reflection should return non-false file');

        $line = $reflection->getStartLine();
        \assert($line !== false, 'Anonymous class reflection should return non-false line');

        return new self(
            file: $file,
            line: $line,
            name: $reflection->name,
        );
    }

    public function toString(): string
    {
        return sprintf('anon.class:%s:%d%s', $this->file, $this->line, $this->column === null ? '' : ':' . $this->column);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->file === $this->file
            && $value->line === $this->line
            && $value->column === $this->column;
    }

    /**
     * @return class-string
     */
    protected function requireNameForReflection(): string
    {
        return $this->name ?? throw new \LogicException(sprintf(
            "Cannot reflect %s, because it's runtime name is not available",
            $this->toString(),
        ));
    }

    public function reflect(): \ReflectionClass
    {
        return new \ReflectionClass($this->requireNameForReflection());
    }

    public function __serialize(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'column' => $this->column,
            'name' => null,
        ];
    }
}
