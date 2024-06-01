<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class ParameterId extends DeclarationId
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly FunctionId|MethodId $function,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s$%s)', substr($this->function->toString(), 0, -1), $this->name);
    }

    public function equals(DeclarationId $id): bool
    {
        return $id instanceof self
            && $id->function->equals($this->function)
            && $id->name === $this->name;
    }

    public function reflect(): \ReflectionParameter
    {
        if ($this->function instanceof FunctionId) {
            return new \ReflectionParameter($this->function->name, $this->name);
        }

        return new \ReflectionParameter([$this->function->class->name, $this->function->name], $this->name);
    }
}
