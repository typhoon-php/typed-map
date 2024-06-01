<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
final class AnonymousClassId extends ClassId
{
    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?positive-int $column
     * @param ?class-string $originalName
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        public readonly ?int $column = null,
        ?string $originalName = null,
    ) {
        parent::__construct($originalName ?? $this->toString());
    }

    public function toString(): string
    {
        return sprintf(
            'anonymous-class:%s:%d%s',
            $this->file,
            $this->line,
            $this->column === null ? '' : ':' . $this->column,
        );
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
