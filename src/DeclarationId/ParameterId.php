<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
final class ParameterId extends Id
{
    /**
     * @param non-empty-string $name
     */
    protected function __construct(
        public readonly NamedFunctionId|AnonymousFunctionId|MethodId $function,
        public readonly string $name,
    ) {}

    public function toString(): string
    {
        return sprintf('%s$%s)', substr($this->function->toString(), 0, -1), $this->name);
    }

    public function equals(mixed $value): bool
    {
        return $value instanceof self
            && $value->function->equals($this->function)
            && $value->name === $this->name;
    }

    public function reflect(): \ReflectionParameter
    {
        if ($this->function instanceof NamedFunctionId) {
            return new \ReflectionParameter($this->function->name, $this->name);
        }

        if ($this->function instanceof AnonymousFunctionId) {
            throw new \LogicException(sprintf('Cannot reflect %s', $this->toString()));
        }

        $class = $this->function->class->name ?? throw new \LogicException(sprintf(
            "Cannot reflect %s, because it's runtime name is not available",
            $this->toString(),
        ));

        return new \ReflectionParameter([$class, $this->function->name], $this->name);
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_PARAMETER, $this->function, $this->name];
    }
}
