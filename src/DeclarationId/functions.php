<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

/**
 * @api
 */
function constantId(string $name): ConstantId
{
    return DeclarationId::constant($name);
}

/**
 * @api
 */
function functionId(string $name): FunctionId
{
    return DeclarationId::function($name);
}

/**
 * @api
 */
function classId(string|object $nameOrObject): ClassId|AnonymousClassId
{
    return DeclarationId::class($nameOrObject);
}

/**
 * @api
 */
function anonymousClassId(string $file, int $line): AnonymousClassId
{
    return DeclarationId::anonymousClass($file, $line);
}

/**
 * @api
 */
function classConstantId(string|ClassId|AnonymousClassId $class, string $name): ClassConstantId
{
    return DeclarationId::classConstant($class, $name);
}

/**
 * @api
 */
function propertyId(string|ClassId|AnonymousClassId $class, string $name): PropertyId
{
    return DeclarationId::property($class, $name);
}

/**
 * @api
 */
function methodId(string|ClassId|AnonymousClassId $class, string $name): MethodId
{
    return DeclarationId::method($class, $name);
}

/**
 * @api
 */
function parameterId(FunctionId|MethodId $function, string $name): ParameterId
{
    return DeclarationId::parameter($function, $name);
}

/**
 * @api
 */
function aliasId(ClassId $class, string $name): AliasId
{
    return DeclarationId::alias($class, $name);
}

/**
 * @api
 */
function templateId(FunctionId|ClassId|AnonymousClassId|MethodId $declaredAt, string $name): TemplateId
{
    return DeclarationId::template($declaredAt, $name);
}
