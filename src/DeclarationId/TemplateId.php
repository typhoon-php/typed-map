<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class TemplateId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly FunctionId|ClassId|MethodId $declaredAt,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s#%s', $this->name, $this->declaredAt->toString());
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->declaredAt->equals($this->declaredAt)
            && $id->name === $this->name;
    }
}
