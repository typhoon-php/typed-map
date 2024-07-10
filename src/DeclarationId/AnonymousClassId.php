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
     * @param positive-int $column
     * @param ?class-string $name
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly int $column,
        public readonly ?string $name = null,
    ) {}

    /**
     * @throws InvalidClassName
     */
    protected static function fromName(string $name): self
    {
        if (preg_match('/@anonymous\x00(.+):(\d+)/', $name, $matches) !== 1) {
            throw new InvalidClassName(sprintf('Invalid anonymous class name "%s"', $name));
        }

        /** @var non-empty-string $file */
        $file = $matches[1];
        $line = (int) $matches[2];

        if ($line <= 0) {
            throw new InvalidClassName(sprintf('Invalid anonymous class name "%s"', $name));
        }

        return new self(
            file: $file,
            line: $line,
            column: self::resolveColumn($name, $file, $line),
            name: class_exists($name, autoload: false) ? $name : null,
        );
    }

    protected static function doFromReflection(\ReflectionClass $reflection): self
    {
        $file = $reflection->getFileName();
        \assert($file !== false);

        $line = $reflection->getStartLine();
        \assert($line !== false);

        return new self(
            file: $file,
            line: $line,
            column: self::resolveColumn($reflection->name, $file, $line),
            name: $reflection->name,
        );
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @return positive-int
     */
    private static function resolveColumn(string $name, string $file, int $line): int
    {
        $code = @file_get_contents($file);

        if ($code === false) {
            throw new InvalidClassName(sprintf(
                'File specified in anonymous class name "%s" does not exist or is not readable',
                $name,
            ));
        }

        $previous = null;
        $position = null;

        foreach (\PhpToken::tokenize($code) as $token) {
            if ($token->isIgnorable()) {
                continue;
            }

            if ($token->line === $line && $token->id === T_CLASS && $previous?->id === T_NEW) {
                if ($position !== null) {
                    throw new \LogicException(sprintf('Multiple anonymous classes declared at %s::%d. Use Id::anonymousClass() to create AnonymousClassId', $file, $line));
                }

                $position = $token->pos;
            }

            if ($token->line > $line) {
                break;
            }

            $previous = $token;
        }

        if ($position === null) {
            throw new InvalidClassName(sprintf('No anonymous classes declared at %s::%d', $file, $line));
        }

        $lineStartPosition = strrpos($code, "\n", $position - \strlen($code));
        \assert($lineStartPosition !== false);
        $column = $position - $lineStartPosition;
        \assert($column > 0);

        return $column;
    }

    public function toString(): string
    {
        return sprintf('anon.class:%s:%d:%d', $this->file, $this->line, $this->column);
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
            throw new AnonymousClassNameNotAvailable(sprintf(
                "Cannot reflect anonymous class %s, because it's runtime name is not available",
                $this->toString(),
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
        ];
    }

    /**
     * @param array{file: non-empty-string, line: positive-int, column: positive-int} $data
     */
    public function __unserialize(array $data): void
    {
        ['file' => $this->file, 'line' => $this->line, 'column' => $this->column] = $data;
        $this->name = null;
    }
}
