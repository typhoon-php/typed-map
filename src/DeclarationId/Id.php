<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
abstract class Id
{
    final public static function constant(string $name): ConstantId
    {
        if (!self::isNameValid($name)) {
            throw new InvalidConstantName(sprintf('Invalid constant name "%s"', $name));
        }

        return new ConstantId($name);
    }

    final public static function namedFunction(string $name): NamedFunctionId
    {
        if (!self::isNameValid($name)) {
            throw new InvalidName('function', $name);
        }

        return new NamedFunctionId($name);
    }

    final public static function anonymousFunction(string $file, int $line, int $column): AnonymousFunctionId
    {
        \assert($file !== '');
        \assert($line > 0);
        \assert($column > 0);

        return new AnonymousFunctionId($file, $line, $column);
    }

    /**
     * @throws InvalidClassName
     */
    final public static function class(string|object $nameOrObject): ClassId
    {
        if (\is_object($nameOrObject)) {
            if (str_contains($nameOrObject::class, '@')) {
                return AnonymousClassId::fromName($nameOrObject::class);
            }

            return new NamedClassId($nameOrObject::class);
        }

        if (str_contains($nameOrObject, '@')) {
            return AnonymousClassId::fromName($nameOrObject);
        }

        if (!self::isNameValid($nameOrObject)) {
            throw new InvalidClassName(sprintf('Invalid class name "%s"', $nameOrObject));
        }

        return new NamedClassId($nameOrObject);
    }

    /**
     * @throws InvalidClassName
     */
    final public static function namedClass(string|object $nameOrObject): NamedClassId
    {
        if (\is_object($nameOrObject)) {
            if (str_contains($nameOrObject::class, '@')) {
                throw new InvalidClassName(sprintf('Invalid non-anonymous class name "%s"', $nameOrObject::class));
            }

            return new NamedClassId($nameOrObject::class);
        }

        if (!self::isNameValid($nameOrObject)) {
            throw new InvalidClassName(sprintf('Invalid non-anonymous class name "%s"', $nameOrObject));
        }

        return new NamedClassId($nameOrObject);
    }

    final public static function anonymousClass(string $file, int $line, int $column): AnonymousClassId
    {
        \assert($file !== '');
        \assert($line > 0);
        \assert($column > 0);

        return new AnonymousClassId($file, $line, $column);
    }

    final public static function classConstant(string|ClassId $class, string $name): ClassConstantId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name));

        return new ClassConstantId($class, $name);
    }

    final public static function property(string|ClassId $class, string $name): PropertyId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name));

        return new PropertyId($class, $name);
    }

    final public static function method(string|ClassId $class, string $name): MethodId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name));

        return new MethodId($class, $name);
    }

    final public static function parameter(FunctionId|MethodId $function, string $name): ParameterId
    {
        \assert(self::isLabelValid($name));

        return new ParameterId($function, $name);
    }

    final public static function alias(string|NamedClassId $class, string $name): AliasId
    {
        if (\is_string($class)) {
            $class = self::namedClass($class);
        }

        \assert(self::isLabelValid($name), sprintf('Invalid alias name "%s"', $name));

        return new AliasId($class, $name);
    }

    final public static function template(FunctionId|ClassId|MethodId $id, string $name): TemplateId
    {
        \assert(self::isLabelValid($name));

        return new TemplateId($id, $name);
    }

    /**
     * @return (
     *     $reflection is \ReflectionFunction ? FunctionId :
     *     $reflection is \ReflectionClass ? ClassId|AnonymousClassId :
     *     $reflection is \ReflectionClassConstant ? ClassConstantId :
     *     $reflection is \ReflectionProperty ? PropertyId :
     *     $reflection is \ReflectionMethod ? MethodId :
     *     $reflection is \ReflectionParameter ? ParameterId : never
     * )
     */
    final public static function fromReflection(\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter $reflection): self
    {
        return match (true) {
            $reflection instanceof \ReflectionFunction => $reflection->name === '{closure}'
                ? AnonymousFunctionId::doFromReflection($reflection)
                : NamedFunctionId::doFromReflection($reflection),
            $reflection instanceof \ReflectionClass => $reflection->isAnonymous()
                ? AnonymousClassId::doFromReflection($reflection)
                : new NamedClassId($reflection->name),
            $reflection instanceof \ReflectionClassConstant => new ClassConstantId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name),
            $reflection instanceof \ReflectionProperty => PropertyId::doFromReflection($reflection),
            $reflection instanceof \ReflectionMethod => new MethodId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name),
            $reflection instanceof \ReflectionParameter => new ParameterId(self::fromReflection($reflection->getDeclaringFunction()), $reflection->name),
        };
    }

    /**
     * @psalm-pure
     * @psalm-assert-if-true non-empty-string $name
     */
    private static function isNameValid(string $name): bool
    {
        return preg_match('/^[a-zA-Z\x80-\xff_][a-zA-Z0-9\x80-\xff_]*(\\\[a-zA-Z\x80-\xff_][a-zA-Z0-9\x80-\xff_]*)*$/', $name) === 1;
    }

    /**
     * @psalm-pure
     * @psalm-assert-if-true non-empty-string $name
     */
    private static function isLabelValid(string $name): bool
    {
        return preg_match('/^[a-zA-Z\x80-\xff_][a-zA-Z0-9\x80-\xff_]*$/', $name) === 1;
    }

    /**
     * @return non-empty-string
     */
    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return non-empty-string
     */
    abstract public function toString(): string;

    abstract public function equals(mixed $value): bool;
}
