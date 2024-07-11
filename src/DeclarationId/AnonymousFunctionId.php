<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class AnonymousFunctionId extends Id
{
    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?positive-int $column
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly ?int $column = null,
    ) {}

    protected static function doFromReflection(\ReflectionFunction $reflection): self
    {
        $file = $reflection->getFileName();
        \assert($file !== false, 'Anonymous function reflection should return non-false file');

        $line = $reflection->getStartLine();
        \assert($line !== false, 'Anonymous function reflection should return non-false line');

        return new self($file, $line);
    }

    public function toString(): string
    {
        return sprintf('anonymous-function:%s:%d%s()', $this->file, $this->line, $this->column === null ? '' : ':' . $this->column);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->file === $this->file
            && $value->line === $this->line
            && $value->column === $this->column;
    }

    public function jsonSerialize(): array
    {
        $data = [self::CODE_ANONYMOUS_FUNCTION, $this->file, $this->line];

        if ($this->column !== null) {
            $data[] = $this->column;
        }

        return $data;
    }
}
