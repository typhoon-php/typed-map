<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @template-covariant TName of ?non-empty-string
 */
final class AnonymousClassId extends Id
{
    /**
     * @param non-empty-string $name
     * @return non-empty-string
     */
    protected static function normalizeClassNameForException(string $name): string
    {
        /** @var non-empty-string */
        return str_replace("\x00", '\0', $name);
    }

    /**
     * @template TTName of non-empty-string
     * @param TTName $name
     * @return (TTName is class-string ? self<TTName> : self<null>)
     */
    protected static function fromName(string $name): self
    {
        if (preg_match('/@anonymous\x00(.+):(\d+)/', $name, $matches) !== 1) {
            throw new \InvalidArgumentException(\sprintf('Invalid anonymous class name "%s"', self::normalizeClassNameForException($name)));
        }

        /** @var non-empty-string $file */
        $file = $matches[1];
        $line = (int) $matches[2];

        if ($line <= 0) {
            throw new \InvalidArgumentException(\sprintf('Invalid anonymous class name "%s"', self::normalizeClassNameForException($name)));
        }

        if (class_exists($name, autoload: false)) {
            /** @var self<TTName> */
            return new self(file: $file, line: $line, name: $name);
        }

        /** @var self<null> */
        return new self(file: $file, line: $line);
    }

    protected static function doFromReflection(\ReflectionClass $reflection): self
    {
        $file = $reflection->getFileName();
        \assert($file !== false, 'Anonymous class reflection should not return false file');

        $line = $reflection->getStartLine();
        \assert($line !== false, 'Anonymous class reflection should not return false line');

        return new self(
            file: $file,
            line: $line,
            name: $reflection->name,
        );
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?positive-int $column
     * @param TName $name
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly ?int $column = null,
        public readonly ?string $name = null,
    ) {}

    /**
     * @param positive-int $column
     * @return self<TName>
     */
    public function withColumn(int $column): self
    {
        return new self(
            file: $this->file,
            line: $this->line,
            column: $column,
            name: $this->name,
        );
    }

    /**
     * @return self<TName>
     */
    public function withoutColumn(): self
    {
        return new self(
            file: $this->file,
            line: $this->line,
            name: $this->name,
        );
    }

    public function describe(): string
    {
        return \sprintf('anonymous class at %s:%d%s', $this->file, $this->line, $this->column === null ? '' : ':' . $this->column);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->file === $this->file
            && $value->line === $this->line
            && $value->column === $this->column;
    }

    public function reflect(): \ReflectionClass
    {
        if ($this->name === null) {
            throw new \LogicException(\sprintf(
                "Cannot reflect %s, because it's runtime name is not available",
                $this->describe(),
            ));
        }

        return new \ReflectionClass($this->name);
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

    public function jsonSerialize(): array
    {
        $data = [self::CODE_ANONYMOUS_CLASS, $this->file, $this->line];

        if ($this->column !== null) {
            $data[] = $this->column;
        }

        return $data;
    }
}
