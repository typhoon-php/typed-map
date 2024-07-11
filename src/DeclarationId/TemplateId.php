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
        public readonly NamedFunctionId|AnonymousFunctionId|NamedClassId|AnonymousClassId|MethodId $site,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s#%s', $this->name, $this->site->toString());
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->site->equals($this->site)
            && $value->name === $this->name;
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_TEMPLATE, $this->site, $this->name];
    }
}
