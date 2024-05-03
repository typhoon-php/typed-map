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
     * @param non-empty-string $id
     * @param ?\class-string $originalName
     */
    protected function __construct(
        private readonly string $id,
        private readonly ?string $originalName = null,
    ) {}

    /**
     * @return class-string
     */
    public function name(): string
    {
        if ($this->originalName !== null) {
            return $this->originalName;
        }

        throw new \LogicException('Runtime anonymous class name is not available');
    }

    public function toString(): string
    {
        return 'anonymous-class:' . $this->id;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'originalName' => null,
        ];
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->id === $this->id;
    }
}
