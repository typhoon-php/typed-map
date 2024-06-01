<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AnonymousClassId extends ClassId
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
        ?string $name = null,
    ) {
        parent::__construct($name ?? $this->toString());
    }

    protected static function fromName(string $name): self
    {
        if (preg_match('/anonymous\x00(.+):(\d+)/', $name, $matches) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid anonymous class name "%s"', $name));
        }

        /** @var non-empty-string $file */
        $file = $matches[1];
        $line = (int) $matches[2];
        \assert($line > 0);

        return new self(
            file: $file,
            line: $line,
            column: self::resolveColumn($file, $line),
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
            column: self::resolveColumn($file, $line),
            name: $reflection->name,
        );
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @return positive-int
     */
    private static function resolveColumn(string $file, int $line): int
    {
        $code = @file_get_contents($file);

        if ($code === false) {
            throw new \RuntimeException(sprintf('File %s does not exist or is not readable', $file));
        }

        $previous = null;
        $position = null;

        foreach (\PhpToken::tokenize($code) as $token) {
            if ($token->isIgnorable()) {
                continue;
            }

            if ($token->line === $line && $token->id === T_CLASS && $previous?->id === T_NEW) {
                if ($position !== null) {
                    throw new \LogicException('Two anonymous classes');
                }

                $position = $token->pos;
            }

            if ($token->line > $line) {
                break;
            }

            $previous = $token;
        }

        if ($position === null) {
            throw new \LogicException('No anonymous class');
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

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->file === $this->file
            && $id->line === $this->line
            && $id->column === $this->column;
    }

    public function __serialize(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'column' => $this->column,
            'name' => $this->toString(),
        ];
    }
}
