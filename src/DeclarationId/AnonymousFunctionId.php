<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
final class AnonymousFunctionId extends FunctionId
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
    ) {
        parent::__construct(sprintf(
            'anonymous-function:%s:%d%s',
            $this->file,
            $this->line,
            $this->column === null ? '' : ':' . $this->column,
        ));
    }

    public function toString(): string
    {
        return $this->name . '()';
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->file === $this->file
            && $id->line === $this->line
            && $id->column === $this->column;
    }
}
