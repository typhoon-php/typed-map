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

    public function describe(): string
    {
        return sprintf('parameter $%s of %s', $this->name, $this->function->describe());
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
            throw new \LogicException(sprintf('Cannot reflect %s', $this->describe()));
        }

        $class = $this->function->class->name ?? throw new \LogicException(sprintf(
            "Cannot reflect %s, because it's runtime name is not available",
            $this->describe(),
        ));

        return new \ReflectionParameter([$class, $this->function->name], $this->name);
    }

    public function jsonSerialize(): array
    {
        return [self::CODE_PARAMETER, $this->function, $this->name];
    }
}
