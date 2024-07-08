<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AnonymousFunctionId extends FunctionId
{
    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param positive-int $column
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly int $column,
    ) {
        parent::__construct(sprintf('anon.function:%s:%d:%d', $this->file, $this->line, $this->column));
    }

    protected static function doFromReflection(\ReflectionFunction $reflection): self
    {
        $file = $reflection->getFileName();
        \assert($file !== false);
        $line = $reflection->getStartLine();
        \assert($line !== false);

        return new self($file, $line, self::resolveColumn($file, $line));
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @return positive-int
     */
    private static function resolveColumn(string $file, int $line): int
    {
        $readFile = @file($file);

        if ($readFile === false) {
            throw new \RuntimeException(sprintf('File %s does not exist or is not readable', $file));
        }

        preg_match_all('/(static\s+)?(fn|function)\s+\(/', $readFile[$line - 1], $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        /** @var list<array{array{non-empty-string, non-negative-int}}> $matches */
        if ($matches === []) {
            throw new \RuntimeException(sprintf('No anonymous functions found at %s:%d', $file, $line));
        }

        if (\count($matches) > 1) {
            throw new \RuntimeException(sprintf(
                'More than one anonymous function defined at %s:%d. Use %s::anonymousFunction() instead',
                $file,
                $line,
                Id::class,
            ));
        }

        return $matches[0][0][1] + 1;
    }

    public function toString(): string
    {
        return $this->name . '()';
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->file === $this->file
            && $value->line === $this->line
            && $value->column === $this->column;
    }
}
