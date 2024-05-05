<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
final class AnonymousClassId extends DeclarationId
{
    /**
     * @var non-empty-string
     */
    public readonly string $name;

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?class-string $originalName
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        ?string $originalName = null,
    ) {
        $this->name = $originalName ?? $this->composeName();
    }

    public function toString(): string
    {
        return sprintf('anonymous-class:%s:%d', $this->file, $this->line);
    }

    public function __serialize(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'name' => $this->composeName(),
        ];
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->file === $this->file
            && $id->line === $this->line;
    }

    /**
     * @return non-empty-string
     */
    private function composeName(): string
    {
        return sprintf("class@anonymous\x00%s:%d", $this->file, $this->line);
    }
}
