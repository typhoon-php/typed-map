<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 * @property-read non-empty-string $name
 */
final class AnonymousClassId extends DeclarationId
{
    /**
     * @var ?non-empty-string
     */
    private ?string $_name;

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
        $this->_name = $originalName;
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     */
    public function __isset(string $name): bool
    {
        return $name === 'name';
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     */
    public function __get(string $name)
    {
        if ($name === 'name') {
            /** @psalm-suppress InaccessibleProperty */
            return $this->_name ??= $this->resolveName();
        }

        throw new \LogicException(sprintf('Property %s::$%s does not exist', self::class, $name));
    }

    public function toString(): string
    {
        return sprintf('anonymous-class:%s:%d', $this->file, $this->line);
    }

    public function __serialize(): array
    {
        return ['file' => $this->file, 'line' => $this->line];
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
