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
     * @param ?class-string $originalName
     */
    protected function __construct(
        public readonly string $file,
        public readonly int $line,
        ?string $originalName = null,
    ) {
        parent::__construct($originalName ?? $this->resolveName());
    }

    public function toString(): string
    {
        return sprintf('anonymous-class:%s:%d', $this->file, $this->line);
    }

    public function __serialize(): array
    {
        return ['file' => $this->file, 'line' => $this->line];
    }

    /**
     * @param array{file: non-empty-string, line: positive-int} $data
     */
    public function __unserialize(array $data): void
    {
        $this->file = $data['file'];
        $this->line = $data['line'];
        /** @psalm-suppress UnusedMethodCall */
        $this->setName($this->resolveName());
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
    private function resolveName(): string
    {
        $needle = sprintf("@anonymous\x00%s:%d", $this->file, $this->line);

        /** @psalm-suppress ImpureFunctionCall */
        foreach (get_declared_classes() as $declaredClass) {
            if (str_contains($declaredClass, $needle)) {
                return $declaredClass;
            }
        }

        return 'class' . $needle;
    }
}
