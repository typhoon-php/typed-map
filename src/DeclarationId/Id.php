<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
abstract class Id
{
    /**
     * @param non-empty-string $name
     */
    final public static function constant(string $name): ConstantId
    {
        return new ConstantId($name);
    }

    /**
     * @param non-empty-string $name
     */
    final public static function namedFunction(string $name): NamedFunctionId
    {
        return new NamedFunctionId($name);
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param positive-int $column
     */
    final public static function anonymousFunction(string $file, int $line, int $column): AnonymousFunctionId
    {
        return new AnonymousFunctionId($file, $line, $column);
    }

    /**
     * @param non-empty-string|object $nameOrObject
     */
    final public static function class(string|object $nameOrObject): NamedClassId|AnonymousClassId
    {
        $name = \is_string($nameOrObject) ? $nameOrObject : $nameOrObject::class;

        if (str_contains($name, '@')) {
            return AnonymousClassId::fromName($name);
        }

        return new NamedClassId($name);
    }

    /**
     * @param non-empty-string|object $nameOrObject
     */
    final public static function namedClass(string|object $nameOrObject): NamedClassId
    {
        $name = \is_string($nameOrObject) ? $nameOrObject : $nameOrObject::class;

        if (str_contains($name, '@')) {
            throw new \InvalidArgumentException();
        }

        return new NamedClassId($name);
    }

    /**
     * @param non-empty-string $file
     * @param positive-int $line
     * @param ?positive-int $column
     * @param ?class-string $name
     */
    final public static function anonymousClass(string $file, int $line, ?int $column = null, ?string $name = null): AnonymousClassId
    {
        return new AnonymousClassId($file, $line, $column, $name);
    }

    /**
     * @param non-empty-string|NamedClassId|AnonymousClassId $class
     * @param non-empty-string $name
     */
    final public static function classConstant(string|NamedClassId|AnonymousClassId $class, string $name): ClassConstantId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        return new ClassConstantId($class, $name);
    }

    /**
     * @param non-empty-string|NamedClassId|AnonymousClassId $class
     * @param non-empty-string $name
     */
    final public static function property(string|NamedClassId|AnonymousClassId $class, string $name): PropertyId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        return new PropertyId($class, $name);
    }

    /**
     * @param non-empty-string|NamedClassId|AnonymousClassId $class
     * @param non-empty-string $name
     */
    final public static function method(string|NamedClassId|AnonymousClassId $class, string $name): MethodId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        return new MethodId($class, $name);
    }

    /**
     * @param non-empty-string $name
     */
    final public static function parameter(FunctionId|MethodId $function, string $name): ParameterId
    {
        return new ParameterId($function, $name);
    }

    /**
     * @param non-empty-string|NamedClassId|AnonymousClassId $class
     * @param non-empty-string $name
     */
    final public static function alias(string|NamedClassId|AnonymousClassId $class, string $name): AliasId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        return new AliasId($class, $name);
    }

    /**
     * @param non-empty-string $name
     */
    final public static function template(FunctionId|NamedClassId|AnonymousClassId|MethodId $id, string $name): TemplateId
    {
        return new TemplateId($id, $name);
    }

    /**
     * @return (
     *     $reflection is \ReflectionFunction ? FunctionId :
     *     $reflection is \ReflectionClass ? NamedClassId|AnonymousClassId :
     *     $reflection is \ReflectionClassConstant ? ClassConstantId :
     *     $reflection is \ReflectionProperty ? PropertyId :
     *     $reflection is \ReflectionMethod ? MethodId :
     *     $reflection is \ReflectionParameter ? ParameterId : never
     * )
     */
    final public static function fromReflection(\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter $reflection): self
    {
        return match (true) {
            $reflection instanceof \ReflectionFunction => $reflection->name === '{closure}' ? AnonymousFunctionId::doFromReflection($reflection) : NamedFunctionId::doFromReflection($reflection),
            $reflection instanceof \ReflectionClass => $reflection->isAnonymous() ? AnonymousClassId::doFromReflection($reflection) : new NamedClassId($reflection->name),
            $reflection instanceof \ReflectionClassConstant => new ClassConstantId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name),
            $reflection instanceof \ReflectionProperty => PropertyId::doFromReflection($reflection),
            $reflection instanceof \ReflectionMethod => new MethodId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name),
            $reflection instanceof \ReflectionParameter => new ParameterId(self::fromReflection($reflection->getDeclaringFunction()), $reflection->name),
        };
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
