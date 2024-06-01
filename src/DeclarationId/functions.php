<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 * @psalm-pure
 */
function constantId(string $name): ConstantId
{
    return DeclarationId::constant($name);
}

/**
 * @api
 * @psalm-pure
 */
function namedFunctionId(string $name): FunctionId
{
    return DeclarationId::namedFunction($name);
}

/**
 * @api
 * @psalm-pure
 */
function classId(string|object $nameOrObject): ClassId
{
    return DeclarationId::class($nameOrObject);
}

/**
 * @api
 * @psalm-pure
 */
function namedClassId(string|object $nameOrObject): NamedClassId
{
    return DeclarationId::namedClass($nameOrObject);
}

/**
 * @api
 * @psalm-pure
 */
function anonymousClassId(string $file, int $line): AnonymousClassId
{
    return DeclarationId::anonymousClass($file, $line);
}

/**
 * @api
 * @psalm-pure
 */
function classConstantId(string|ClassId $class, string $name): ClassConstantId
{
    return DeclarationId::classConstant($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function propertyId(string|ClassId $class, string $name): PropertyId
{
    return DeclarationId::property($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function methodId(string|ClassId $class, string $name): MethodId
{
    return DeclarationId::method($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function parameterId(FunctionId|MethodId $function, string $name): ParameterId
{
    return DeclarationId::parameter($function, $name);
}

/**
 * @api
 * @psalm-pure
 */
function aliasId(string|NamedClassId $class, string $name): AliasId
{
    return DeclarationId::alias($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function templateId(FunctionId|ClassId|MethodId $declaredAt, string $name): TemplateId
{
    return DeclarationId::template($declaredAt, $name);
}

/**
 * @api
 * @return (
 *     $reflection is \ReflectionFunction ? FunctionId :
 *     $reflection is \ReflectionClass ? ClassId|AnonymousClassId :
 *     $reflection is \ReflectionClassConstant ? ClassConstantId :
 *     $reflection is \ReflectionProperty ? PropertyId :
 *     $reflection is \ReflectionMethod ? MethodId :
 *     $reflection is \ReflectionParameter ? ParameterId : never
 * )
 * @psalm-suppress InvalidReturnType, NoValue
 */
function nativeReflectionId(\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter $reflection): DeclarationId
{
    return DeclarationId::fromReflection($reflection);
}

/**
 * @api
 * @return (
 *     $id is FunctionId ? \ReflectionFunction :
 *     $id is ClassId|AnonymousClassId ? \ReflectionClass :
 *     $id is ClassConstantId ? \ReflectionClassConstant :
 *     $id is PropertyId ? \ReflectionProperty :
 *     $id is MethodId ? \ReflectionMethod :
 *     $id is ParameterId ? \ReflectionParameter : never
 *  )
 * @psalm-suppress ArgumentTypeCoercion
 */
function nativeReflectionById(DeclarationId $id): \ReflectionFunction|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionMethod|\ReflectionParameter
{
    return match (true) {
        $id instanceof FunctionId => new \ReflectionFunction($id->name),
        $id instanceof NamedClassId, $id instanceof AnonymousClassId => new \ReflectionClass($id->name),
        $id instanceof ClassConstantId => new \ReflectionClassConstant($id->class->name, $id->name),
        $id instanceof PropertyId => new \ReflectionProperty($id->class->name, $id->name),
        $id instanceof MethodId => new \ReflectionMethod($id->class->name, $id->name),
        $id instanceof ParameterId => new \ReflectionParameter(
            function: $id->function instanceof FunctionId ? $id->function->name : [$id->function->class->name, $id->function->name],
            param: $id->name,
        ),
    };
}
