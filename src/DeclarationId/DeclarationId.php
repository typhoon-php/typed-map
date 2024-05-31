<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-immutable
 */
abstract class DeclarationId
{
    protected function __construct() {}

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function constant(string $name): ConstantId
    {
        \assert(self::isNameValid($name), sprintf('Invalid constant name "%s"', $name));

        return new ConstantId($name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function function(string $name): FunctionId
    {
        \assert(self::isNameValid($name), sprintf('Invalid function name "%s"', $name));

        return new FunctionId($name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function class(string|object $nameOrObject): ClassId
    {
        if (\is_object($nameOrObject)) {
            $name = $nameOrObject::class;

            if (!str_contains($name, '@')) {
                return new NamedClassId($name);
            }
        } else {
            $name = $nameOrObject;

            if (!str_contains($name, '@')) {
                \assert(self::isNameValid($name), sprintf('Invalid class name "%s"', $name));

                return new NamedClassId($name);
            }
        }

        $matched = preg_match('/anonymous\x00(.+):(\d+)/', $name, $matches) === 1;
        \assert($matched, sprintf('Invalid anonymous class name "%s"', $name));

        /** @var non-empty-string $file */
        $file = $matches[1];

        $line = (int) $matches[2];
        \assert($line > 0, 'Anonymous class line must be a positive int');

        /** @psalm-suppress ImpureFunctionCall */
        return new AnonymousClassId(
            file: $file,
            line: $line,
            originalName: class_exists($name, autoload: false) ? $name : null,
        );
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function namedClass(string|object $nameOrObject): NamedClassId
    {
        if (\is_object($nameOrObject)) {
            $name = $nameOrObject::class;

            \assert(!str_contains($name, '@'), sprintf('Invalid class name "%s"', $name));

            return new NamedClassId($name);
        }

        $name = $nameOrObject;

        \assert(self::isNameValid($name), sprintf('Invalid class name "%s"', $name));

        return new NamedClassId($name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function anonymousClass(string $file, int $line): AnonymousClassId
    {
        \assert($file !== '', 'Anonymous class file must not be empty');
        \assert($line > 0, 'Anonymous class line must be a positive int');

        return new AnonymousClassId($file, $line);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function classConstant(string|ClassId $class, string $name): ClassConstantId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name), sprintf('Invalid class constant name "%s"', $name));

        return new ClassConstantId($class, $name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function property(string|ClassId $class, string $name): PropertyId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name), sprintf('Invalid property name "%s"', $name));

        return new PropertyId($class, $name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function method(string|ClassId $class, string $name): MethodId
    {
        if (\is_string($class)) {
            $class = self::class($class);
        }

        \assert(self::isLabelValid($name), sprintf('Invalid method name "%s"', $name));

        return new MethodId($class, $name);
    }

    /**
     * @psalm-pure
     */
    final public static function parameter(FunctionId|MethodId $function, string $name): ParameterId
    {
        \assert(self::isLabelValid($name), sprintf('Invalid parameter name "%s"', $name));

        return new ParameterId($function, $name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function alias(string|NamedClassId $class, string $name): AliasId
    {
        if (\is_string($class)) {
            $class = self::namedClass($class);
        }

        \assert(self::isLabelValid($name), sprintf('Invalid alias name "%s"', $name));

        return new AliasId($class, $name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @psalm-pure
     */
    final public static function template(FunctionId|ClassId|MethodId $declaredAt, string $name): TemplateId
    {
        \assert(self::isLabelValid($name), sprintf('Invalid template name "%s"', $name));

        return new TemplateId($declaredAt, $name);
    }

    /**
     * @internal
     * @psalm-internal Typhoon\DeclarationId
     * @return (
     *     $reflection is \ReflectionFunction ? FunctionId :
     *     $reflection is \ReflectionClass ? ClassId :
     *     $reflection is \ReflectionClassConstant ? ClassConstantId :
     *     $reflection is \ReflectionProperty ? PropertyId :
     *     $reflection is \ReflectionMethod ? MethodId :
     *     $reflection is \ReflectionParameter ? ParameterId : never
     * )
     */
    final public static function fromReflection(\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter $reflection): self
    {
        if ($reflection instanceof \ReflectionFunction) {
            \assert($reflection->name !== '');

            return new FunctionId($reflection->name);
        }

        if ($reflection instanceof \ReflectionClass) {
            if ($reflection->isAnonymous()) {
                return new AnonymousClassId($reflection->getFileName(), $reflection->getStartLine(), $reflection->name);
            }

            return new NamedClassId($reflection->name);
        }

        if ($reflection instanceof \ReflectionClassConstant) {
            return new ClassConstantId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name);
        }

        if ($reflection instanceof \ReflectionProperty) {
            /** @psalm-suppress RedundantCondition */
            \assert($reflection->isDefault() && $reflection->name !== '');

            return new PropertyId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name);
        }

        if ($reflection instanceof \ReflectionMethod) {
            return new MethodId(self::fromReflection($reflection->getDeclaringClass()), $reflection->name);
        }

        if ($reflection instanceof \ReflectionParameter) {
            return new ParameterId(self::fromReflection($reflection->getDeclaringFunction()), $reflection->name);
        }

        /** @var object $reflection */
        throw new \InvalidArgumentException(sprintf('%s cannot be identified', $reflection::class));
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

    abstract public function equals(self $id): bool;
}
