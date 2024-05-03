<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
abstract class DeclarationId
{
    final public static function function(string $name): FunctionId
    {
        if (\function_exists($name) || self::isNameValid($name)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            return new FunctionId($name);
        }

        throw new \InvalidArgumentException(sprintf('Invalid function name %s', $name));
    }

    final public static function class(string|object $nameOrObject): ClassId|AnonymousClassId
    {
        $name = \is_object($nameOrObject) ? $nameOrObject::class : $nameOrObject;

        if (str_contains($name, '@')) {
            if (preg_match('/@anonymous\x00(.+):(\d+)/', $name, $matches) !== 1) {
                throw new \InvalidArgumentException(sprintf('Invalid class name %s', $name));
            }

            return new AnonymousClassId(
                id: $matches[1] . ':' . $matches[2],
                originalName: class_exists($name, autoload: false) ? $name : null,
            );
        }

        if (class_exists($name, autoload: false)
            || interface_exists($name, autoload: false)
            || trait_exists($name, autoload: false)
            || self::isNameValid($name)
        ) {
            /** @psalm-suppress ArgumentTypeCoercion */
            return new ClassId($name);
        }

        throw new \InvalidArgumentException(sprintf('Invalid class name %s', $name));
    }

    final public static function anonymousClassFromFile(string $file, int $line): AnonymousClassId
    {
        return new AnonymousClassId($file . ':' . $line);
    }

    /**
     * @param non-empty-string $id
     */
    final public static function anonymousClass(string $id): AnonymousClassId
    {
        return new AnonymousClassId($id);
    }

    final public static function classConstant(string|ClassId|AnonymousClassId $class, string $name): ClassConstantId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        if (!self::isLabelValid($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid class constant name %s', $name));
        }

        return new ClassConstantId($class, $name);
    }

    final public static function property(string|ClassId|AnonymousClassId $class, string $name): PropertyId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        if (!self::isLabelValid($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid property name %s', $name));
        }

        return new PropertyId($class, $name);
    }

    final public static function method(string|ClassId|AnonymousClassId $class, string $name): MethodId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        if (!self::isLabelValid($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid method name %s', $name));
        }

        return new MethodId($class, $name);
    }

    final public static function parameter(FunctionId|MethodId $function, string $name): ParameterId
    {
        if (!self::isLabelValid($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid parameter name %s', $name));
        }

        return new ParameterId($function, $name);
    }

    final public static function template(FunctionId|ClassId|AnonymousClassId|MethodId $declaredAt, string $name): TemplateId
    {
        if (!self::isLabelValid($name)) {
            throw new \InvalidArgumentException(sprintf('Invalid template name %s', $name));
        }

        return new TemplateId($declaredAt, $name);
    }

    /**
     * @psalm-assert-if-true non-empty-string $name
     */
    private static function isNameValid(string $name): bool
    {
        return preg_match('/^[a-zA-Z\x80-\xff_][a-zA-Z0-9\x80-\xff_]*(\\\[a-zA-Z\x80-\xff_][a-zA-Z0-9\x80-\xff_]*)*$/', $name) === 1;
    }

    /**
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

    abstract public function equals(self $id): bool;
}
