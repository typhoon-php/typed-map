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
function functionId(string $name): FunctionId
{
    return DeclarationId::function($name);
}

/**
 * @api
 * @psalm-pure
 */
function anyClassId(string|object $nameOrObject): ClassId|AnonymousClassId
{
    return DeclarationId::anyClass($nameOrObject);
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
function anonymousClassId(string $file, int $line): AnonymousClassId
{
    return DeclarationId::anonymousClass($file, $line);
}

/**
 * @api
 * @psalm-pure
 */
function classConstantId(string|ClassId|AnonymousClassId $class, string $name): ClassConstantId
{
    return DeclarationId::classConstant($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function propertyId(string|ClassId|AnonymousClassId $class, string $name): PropertyId
{
    return DeclarationId::property($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function methodId(string|ClassId|AnonymousClassId $class, string $name): MethodId
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
function aliasId(string|ClassId $class, string $name): AliasId
{
    return DeclarationId::alias($class, $name);
}

/**
 * @api
 * @psalm-pure
 */
function templateId(FunctionId|ClassId|AnonymousClassId|MethodId $declaredAt, string $name): TemplateId
{
    return DeclarationId::template($declaredAt, $name);
}
