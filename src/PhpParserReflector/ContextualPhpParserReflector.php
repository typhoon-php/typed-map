<?php

declare(strict_types=1);

namespace Typhoon\Reflection\PhpParserReflector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Typhoon\Reflection\FileResource;
use Typhoon\Reflection\Metadata\AttributeMetadata;
use Typhoon\Reflection\Metadata\ClassMetadata;
use Typhoon\Reflection\Metadata\MethodMetadata;
use Typhoon\Reflection\Metadata\ParameterMetadata;
use Typhoon\Reflection\Metadata\PropertyMetadata;
use Typhoon\Reflection\Metadata\TypeMetadata;
use Typhoon\Reflection\PhpDocParser\ContextualPhpDocTypeReflector;
use Typhoon\Reflection\PhpDocParser\PhpDoc;
use Typhoon\Reflection\PhpDocParser\PhpDocParser;
use Typhoon\Reflection\TemplateReflection;
use Typhoon\Reflection\TypeAlias\ImportedTypeAlias;
use Typhoon\Reflection\TypeContext\TypeContext;
use Typhoon\Type;
use Typhoon\Type\types;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection\PhpParserReflector
 */
final class ContextualPhpParserReflector
{
    private ContextualPhpDocTypeReflector $phpDocTypeReflector;

    public function __construct(
        private readonly PhpDocParser $phpDocParser,
        private TypeContext $typeContext,
        private readonly FileResource $file,
    ) {
        $this->phpDocTypeReflector = new ContextualPhpDocTypeReflector($typeContext);
    }

    /**
     * @return class-string
     */
    public function resolveClassName(Node\Identifier $name): string
    {
        return $this->typeContext->resolveNameAsClass($name->name);
    }

    /**
     * @template TObject of object
     * @param class-string<TObject> $name
     * @return ClassMetadata<TObject>
     */
    public function reflectClass(Stmt\ClassLike $node, string $name): ClassMetadata
    {
        $phpDoc = $this->parsePhpDoc($node);

        return $this->executeWithTypes(types::atClass($name), $phpDoc, fn(): ClassMetadata => new ClassMetadata(
            changeDetector: $this->file->changeDetector(),
            name: $name,
            modifiers: ClassReflections::modifiers($node),
            internal: $this->file->isInternal(),
            extension: $this->file->extension,
            file: $this->file->isInternal() ? false : $this->file->file,
            startLine: $this->reflectLine($node->getStartLine()),
            endLine: $this->reflectLine($node->getEndLine()),
            docComment: $this->reflectDocComment($node),
            attributes: $this->reflectAttributes($node->attrGroups, \Attribute::TARGET_CLASS),
            typeAliases: $this->reflectTypeAliasesFromContext($phpDoc),
            templates: $this->reflectTemplatesFromContext($phpDoc),
            interface: $node instanceof Stmt\Interface_,
            enum: $node instanceof Stmt\Enum_,
            trait: $node instanceof Stmt\Trait_,
            anonymous: $node->name === null,
            deprecated: $phpDoc->isDeprecated(),
            parentType: $this->reflectParentType($node, $phpDoc),
            ownInterfaceTypes: $this->reflectOwnInterfaceTypes($node, $phpDoc),
            ownProperties: $this->reflectOwnProperties(class: $name, classNode: $node),
            ownMethods: $this->reflectOwnMethods(class: $name, classNode: $node),
        ));
    }

    public function __clone()
    {
        $this->typeContext = clone $this->typeContext;
        $this->phpDocTypeReflector = new ContextualPhpDocTypeReflector($this->typeContext);
    }

    /**
     * @param array<Node\AttributeGroup> $attrGroups
     * @param \Attribute::TARGET_* $target
     * @return list<AttributeMetadata>
     */
    private function reflectAttributes(array $attrGroups, int $target): array
    {
        /** @var list<class-string> */
        $names = [];
        /** @var array<class-string, bool> */
        $repeated = [];

        foreach ($attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $name = $this->typeContext->resolveNameAsClass($attr->name->toCodeString());

                if (str_starts_with($name, 'JetBrains\PhpStorm\Internal')) {
                    continue;
                }

                $names[] = $name;

                if (isset($repeated[$name])) {
                    $repeated[$name] = true;
                } else {
                    $repeated[$name] = false;
                }
            }
        }

        $attributes = [];

        foreach ($names as $position => $name) {
            $attributes[] = new AttributeMetadata(
                name: $name,
                position: $position,
                target: $target,
                repeated: $repeated[$name],
            );
        }

        return $attributes;
    }

    private function reflectParentType(Stmt\ClassLike $node, PhpDoc $phpDoc): ?Type\NamedObjectType
    {
        if (!$node instanceof Stmt\Class_ || $node->extends === null) {
            return null;
        }

        $parentClass = $this->typeContext->resolveNameAsClass($node->extends->toCodeString());

        foreach ($phpDoc->extendedTypes() as $phpDocExtendedType) {
            /** @var Type\NamedObjectType $extendedType */
            $extendedType = $this->phpDocTypeReflector->reflect($phpDocExtendedType);

            if ($extendedType->class === $parentClass) {
                return $extendedType;
            }
        }

        return types::object($parentClass);
    }

    /**
     * @return list<Type\NamedObjectType>
     */
    private function reflectOwnInterfaceTypes(Stmt\ClassLike $node, PhpDoc $phpDoc): array
    {
        if ($node instanceof Stmt\Interface_) {
            $interfaceNames = $node->extends;
            $phpDocInterfaceTypes = $phpDoc->extendedTypes();
        } elseif ($node instanceof Stmt\Class_) {
            $interfaceNames = $node->implements;
            $phpDocInterfaceTypes = $phpDoc->implementedTypes();
        } elseif ($node instanceof Stmt\Enum_) {
            $interfaceNames = [
                ...$node->implements,
                new Name\FullyQualified(\UnitEnum::class),
                ...($node->scalarType === null ? [] : [new Name\FullyQualified(\BackedEnum::class)]),
            ];
            $phpDocInterfaceTypes = $phpDoc->implementedTypes();
        } else {
            return [];
        }

        if ($interfaceNames === []) {
            return [];
        }

        $phpDocInterfaceTypesByClass = [];

        foreach ($phpDocInterfaceTypes as $phpDocInterfaceType) {
            /** @var Type\NamedObjectType $implementedType */
            $implementedType = $this->phpDocTypeReflector->reflect($phpDocInterfaceType);
            $phpDocInterfaceTypesByClass[$implementedType->class] = $implementedType;
        }

        $reflectedInterfaceTypes = [];

        foreach ($interfaceNames as $interfaceName) {
            $interfaceNameAsString = $interfaceName->toCodeString();

            // https://github.com/phpstan/phpstan/issues/8889
            if (\in_array($interfaceNameAsString, ['iterable', 'callable'], true)) {
                continue;
            }

            $interface = $this->typeContext->resolveNameAsClass($interfaceNameAsString);
            $reflectedInterfaceTypes[] = $phpDocInterfaceTypesByClass[$interface] ?? types::object($interface);
        }

        return $reflectedInterfaceTypes;
    }

    /**
     * @param class-string $class
     * @return list<PropertyMetadata>
     */
    private function reflectOwnProperties(string $class, Stmt\ClassLike $classNode): array
    {
        $classReadOnly = $classNode instanceof Stmt\Class_ && $classNode->isReadonly();
        $properties = [];

        if ($classNode instanceof Stmt\Enum_) {
            $properties[] = EnumReflections::name($class);

            if ($classNode->scalarType !== null) {
                $properties[] = EnumReflections::value($class, $this->reflectType($classNode->scalarType));
            }
        }

        foreach ($classNode->getProperties() as $node) {
            $phpDoc = $this->parsePhpDoc($node);
            $type = $this->reflectType($node->type, $phpDoc->varType());

            foreach ($node->props as $property) {
                $properties[] = new PropertyMetadata(
                    name: $property->name->name,
                    class: $class,
                    modifiers: PropertyReflections::modifiers($node, $classReadOnly),
                    type: $type,
                    docComment: $this->reflectDocComment($node),
                    hasDefaultValue: $property->default !== null || $node->type === null,
                    deprecated: $phpDoc->isDeprecated(),
                    startLine: $this->reflectLine($node->getStartLine()),
                    endLine: $this->reflectLine($node->getEndLine()),
                    attributes: $this->reflectAttributes($node->attrGroups, \Attribute::TARGET_PROPERTY),
                );
            }
        }

        $constructorNode = $classNode->getMethod('__construct');

        if ($constructorNode === null) {
            return $properties;
        }

        $phpDoc = $this->parsePhpDoc($constructorNode);

        foreach ($constructorNode->params as $node) {
            $modifiers = PropertyReflections::promotedModifiers($node, $classReadOnly);

            if ($modifiers === 0) {
                continue;
            }

            \assert($node->var instanceof Expr\Variable && \is_string($node->var->name));
            $name = $node->var->name;
            $properties[] = new PropertyMetadata(
                name: $name,
                class: $class,
                modifiers: $modifiers,
                type: $this->reflectType($node->type, $phpDoc->paramTypes()[$name] ?? null),
                docComment: $this->reflectDocComment($node),
                hasDefaultValue: $node->default !== null || $node->type === null,
                promoted: true,
                deprecated: $phpDoc->isDeprecated(),
                startLine: $this->reflectLine($node->getStartLine()),
                endLine: $this->reflectLine($node->getEndLine()),
                attributes: $this->reflectAttributes($node->attrGroups, \Attribute::TARGET_PROPERTY),
            );
        }

        return $properties;
    }

    /**
     * @param class-string $class
     * @return list<MethodMetadata>
     */
    private function reflectOwnMethods(string $class, Stmt\ClassLike $classNode): array
    {
        $interface = $classNode instanceof Stmt\Interface_;
        $methods = [];

        foreach ($classNode->getMethods() as $node) {
            $name = $node->name->name;
            $phpDoc = $this->parsePhpDoc($node);
            $declaredAt = types::atMethod($class, $name);
            $methods[] = $this->executeWithTypes($declaredAt, $phpDoc, fn(): MethodMetadata => new MethodMetadata(
                name: $name,
                class: $class,
                modifiers: MethodReflections::modifiers($node, $interface),
                parameters: $this->reflectParameters($node->params, $phpDoc, $class, $name),
                returnType: $this->reflectType($node->returnType, $phpDoc->returnType()),
                templates: $this->reflectTemplatesFromContext($phpDoc),
                docComment: $this->reflectDocComment($node),
                internal: $this->file->isInternal(),
                extension: $this->file->extension,
                file: $this->file->isInternal() ? false : $this->file->file,
                startLine: $this->reflectLine($node->getStartLine()),
                endLine: $this->reflectLine($node->getEndLine()),
                returnsReference: $node->byRef,
                generator: MethodReflections::isGenerator($node),
                deprecated: $phpDoc->isDeprecated(),
                attributes: $this->reflectAttributes($node->attrGroups, \Attribute::TARGET_METHOD),
            ));
        }

        if ($classNode instanceof Stmt\Enum_) {
            $methods[] = EnumReflections::cases($class);

            if ($classNode->scalarType !== null) {
                $valueType = $this->reflectType($classNode->scalarType);
                $methods[] = EnumReflections::from($class, $valueType);
                $methods[] = EnumReflections::tryFrom($class, $valueType);
            }
        }

        return $methods;
    }

    /**
     * @param array<Node\Param> $nodes
     * @param class-string $class
     * @param non-empty-string $functionOrMethod
     * @return list<ParameterMetadata>
     */
    private function reflectParameters(array $nodes, PhpDoc $methodPhpDoc, string $class, string $functionOrMethod): array
    {
        $parameters = [];
        $isOptional = false;

        foreach (array_values($nodes) as $position => $node) {
            \assert($node->var instanceof Expr\Variable && \is_string($node->var->name));
            $name = $node->var->name;
            $phpDoc = $this->parsePhpDoc($node);
            $isOptional = $isOptional || $node->default !== null || $node->variadic;
            $parameters[] = new ParameterMetadata(
                position: $position,
                name: $name,
                class: $class,
                functionOrMethod: $functionOrMethod,
                type: $this->reflectType($node->type, $methodPhpDoc->paramTypes()[$name] ?? null, ParameterReflections::isDefaultNull($node)),
                passedByReference: $node->byRef,
                defaultValueAvailable: $node->default !== null,
                optional: $isOptional,
                variadic: $node->variadic,
                promoted: ParameterReflections::isPromoted($node),
                deprecated: $phpDoc->isDeprecated(),
                startLine: $this->reflectLine($node->getStartLine()),
                endLine: $this->reflectLine($node->getEndLine()),
                attributes: $this->reflectAttributes($node->attrGroups, \Attribute::TARGET_PARAMETER),
            );
        }

        return $parameters;
    }

    private function reflectType(?Node $native = null, ?TypeNode $phpDoc = null, bool $implicitlyNullable = false): TypeMetadata
    {
        return TypeMetadata::create(
            native: $native === null ? null : NativeTypeReflections::reflect($this->typeContext, $native, $implicitlyNullable),
            phpDoc: $phpDoc === null ? null : $this->phpDocTypeReflector->reflect($phpDoc),
        );
    }

    /**
     * @return list<TemplateReflection>
     */
    private function reflectTemplatesFromContext(PhpDoc $phpDoc): array
    {
        $reflections = [];

        foreach ($phpDoc->templates() as $position => $node) {
            $templateType = $this->typeContext->resolveNameAsType($node->name);
            \assert($templateType instanceof Type\TemplateType);
            $reflections[] = new TemplateReflection(
                name: $node->name,
                position: $position,
                constraint: $templateType->constraint,
                variance: PhpDoc::templateTagVariance($node),
            );
        }

        return $reflections;
    }

    /**
     * @return array<non-empty-string, Type\Type>
     */
    private function reflectTypeAliasesFromContext(PhpDoc $phpDoc): array
    {
        $typeAliases = [];

        foreach ($phpDoc->typeAliases() as $typeAlias) {
            $typeAliases[$typeAlias->alias] = $this->typeContext->resolveNameAsType($typeAlias->alias);
        }

        foreach ($phpDoc->typeAliasImports() as $typeImport) {
            $alias = $typeImport->importedAs ?? $typeImport->importedAlias;
            $typeAliases[$alias] = $this->typeContext->resolveNameAsType($alias);
        }

        return $typeAliases;
    }

    /**
     * @template TReturn
     * @param \Closure(): TReturn $action
     * @return TReturn
     */
    private function executeWithTypes(Type\AtClass|Type\AtMethod $declaredAt, PhpDoc $phpDoc, \Closure $action): mixed
    {
        $class = match (true) {
            $declaredAt instanceof Type\AtClass => $declaredAt->name,
            $declaredAt instanceof Type\AtMethod => $declaredAt->class,
            default => null,
        };
        $types = [];

        foreach ($phpDoc->typeAliases() as $typeAlias) {
            $types[$typeAlias->alias] = fn(): Type\Type => $this->phpDocTypeReflector->reflect($typeAlias->type);
        }

        foreach ($phpDoc->typeAliasImports() as $typeImport) {
            $alias = $typeImport->importedAs ?? $typeImport->importedAlias;
            $types[$alias] = function () use ($class, $typeImport): Type\Type {
                $fromClass = $this->typeContext->resolveNameAsClass($typeImport->importedFrom->name);

                if ($fromClass === $class) {
                    return $this->typeContext->resolveNameAsType($typeImport->importedAlias);
                }

                return new ImportedTypeAlias($fromClass, $typeImport->importedAlias);
            };
        }

        foreach ($phpDoc->templates() as $template) {
            $types[$template->name] = fn(): Type\TemplateType => types::template(
                name: $template->name,
                declaredAt: $declaredAt,
                constraint: $template->bound === null ? types::mixed : $this->phpDocTypeReflector->reflect($template->bound),
            );
        }

        return $this->typeContext->executeWithTypes($action, $types);
    }

    private function parsePhpDoc(Node $node): PhpDoc
    {
        $text = $node->getDocComment()?->getText();

        if ($text === null || $text === '') {
            return PhpDoc::empty();
        }

        return $this->phpDocParser->parsePhpDoc($text);
    }

    /**
     * @return non-empty-string|false
     */
    private function reflectDocComment(Node $node): string|false
    {
        if ($this->file->isInternal()) {
            return false;
        }

        $text = $node->getDocComment()?->getText() ?? '';

        return $text ?: false;
    }

    /**
     * @return positive-int|false
     */
    private function reflectLine(int $line): int|false
    {
        if ($this->file->isInternal()) {
            return false;
        }

        return $line > 0 ? $line : false;
    }
}
