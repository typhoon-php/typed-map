<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class TemplateId extends Id
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

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->declaredAt->equals($this->declaredAt)
            && $value->name === $this->name;
    }
}
