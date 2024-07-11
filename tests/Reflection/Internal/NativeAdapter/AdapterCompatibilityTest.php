<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\NativeAdapter;

use Mockery\Loader\RequireLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Traits\Trait1;
use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\Reflector;
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\Variance;

#[CoversClass(AttributeAdapter::class)]
#[CoversClass(ClassConstantAdapter::class)]
#[CoversClass(ClassAdapter::class)]
#[CoversClass(MethodAdapter::class)]
#[CoversClass(ParameterAdapter::class)]
#[CoversClass(PropertyAdapter::class)]
#[CoversClass(NamedTypeAdapter::class)]
#[CoversClass(ToNativeTypeConverter::class)]
#[CoversClass(UnionTypeAdapter::class)]
#[CoversClass(IntersectionTypeAdapter::class)]
final class AdapterCompatibilityTest extends TestCase
{
    private const MOCKS_DIR = __DIR__ . '/../../../../var/mocks';

    private static Reflector $typhoonReflector;

    public static function setUpBeforeClass(): void
    {
        (new Filesystem())->mkdir(self::MOCKS_DIR);
        \Mockery::setLoader(new RequireLoader(self::MOCKS_DIR));
        self::$typhoonReflector = TyphoonReflector::build();
    }

    /**
     * @param class-string $class
     */
    #[DataProviderExternal(FixturesProvider::class, 'classes')]
    public function testItReflectsClassesCompatiblyViaPhpParserReflector(string $class): void
    {
        $native = new \ReflectionClass($class);

        $typhoon = self::$typhoonReflector->reflectClassLike($class)->toNative();

        $this->assertClassEquals($native, $typhoon);
    }

    private function assertClassEquals(\ReflectionClass $native, \ReflectionClass $typhoon): void
    {
        self::assertSame($native->name, $typhoon->name, 'class.name');
        self::assertSame($native->__toString(), $typhoon->__toString(), 'class.__toString()');
        self::assertAttributesEqual($native->getAttributes(), $typhoon->getAttributes(), 'class.getAttributes()');
        self::assertSame($native->getConstructor()?->name, $typhoon->getConstructor()?->name, 'class.getConstructor().name');
        self::assertSame($native->getDefaultProperties(), $typhoon->getDefaultProperties(), 'class.getDefaultProperties()');
        self::assertSame($native->getDocComment(), $typhoon->getDocComment(), 'class.getDocComment()');
        self::assertSame($native->getEndLine(), $typhoon->getEndLine(), 'class.getEndLine()');
        self::assertEquals($native->getExtension(), $typhoon->getExtension(), 'class.getExtension()');
        self::assertEquals($native->getExtensionName(), $typhoon->getExtensionName(), 'class.getExtensionName()');
        self::assertSame($native->getFileName(), $typhoon->getFileName(), 'class.getFileName()');
        self::assertSame($native->getInterfaceNames(), $typhoon->getInterfaceNames(), 'class.getInterfaceNames()');
        $this->assertReflectionsEqual($native->getInterfaces(), $typhoon->getInterfaces(), 'class.getInterfaces()');
        self::assertSame($native->getModifiers(), $typhoon->getModifiers(), 'class.getModifiers()');
        self::assertSame($native->getName(), $typhoon->getName(), 'class.getName()');
        self::assertSame($native->getNamespaceName(), $typhoon->getNamespaceName(), 'class.getNamespaceName()');
        self::assertSame(($native->getParentClass() ?: null)?->name, ($typhoon->getParentClass() ?: null)?->name, 'class.getParentClass().name');
        self::assertSame($native->getShortName(), $typhoon->getShortName(), 'class.getShortName()');
        self::assertSame($native->getStartLine(), $typhoon->getStartLine(), 'class.getStartLine()');
        self::assertSame($native->getStaticProperties(), $typhoon->getStaticProperties(), 'class.getStaticProperties()');
        // TODO getStaticPropertyValue()
        self::assertSame($native->getTraitAliases(), $typhoon->getTraitAliases(), 'class.getTraitAliases()');
        self::assertSame($native->getTraitNames(), $typhoon->getTraitNames(), 'class.getTraitNames()');
        $this->assertReflectionsEqual($native->getTraits(), $typhoon->getTraits(), 'class.getTraits()');
        foreach ($this->getClasses($native) as $class) {
            $this->assertResultOrExceptionEqual(
                native: static fn(): bool => $native->implementsInterface($class),
                typhoon: static fn(): bool => $typhoon->implementsInterface($class),
                messagePrefix: "class.implementsInterface({$class})",
            );
        }
        self::assertSame($native->inNamespace(), $typhoon->inNamespace(), 'class.inNamespace()');
        self::assertSame($native->isAbstract(), $typhoon->isAbstract(), 'class.isAbstract()');
        self::assertSame($native->isAnonymous(), $typhoon->isAnonymous(), 'class.isAnonymous()');
        self::assertSame($native->isCloneable(), $typhoon->isCloneable(), 'class.isCloneable()');
        self::assertSame($native->isEnum(), $typhoon->isEnum(), 'class.isEnum()');
        self::assertSame($native->isFinal(), $typhoon->isFinal(), 'class.isFinal()');
        if ($this->canCreateMockObject($native)) {
            self::assertSame($native->isInstance($this->createMockObject($native)), $typhoon->isInstance($this->createMockObject($native)), 'class.isInstance()');
        }
        self::assertSame($native->isInstantiable(), $typhoon->isInstantiable(), 'class.isInstantiable()');
        self::assertSame($native->isInterface(), $typhoon->isInterface(), 'class.isInterface()');
        self::assertSame($native->isInternal(), $typhoon->isInternal(), 'class.isInternal()');
        self::assertSame($native->isIterable(), $typhoon->isIterable(), 'class.isIterable()');
        self::assertSame($native->isIterateable(), $typhoon->isIterateable(), 'class.isIterateable()');
        if (method_exists(\ReflectionClass::class, 'isReadOnly')) {
            /** @psalm-suppress MixedArgument, UnusedPsalmSuppress */
            self::assertSame($native->isReadOnly(), $typhoon->isReadOnly(), 'class.isReadOnly()');
        }
        foreach ($this->getClasses($native) as $class) {
            $this->assertResultOrExceptionEqual(
                native: static fn(): bool => $native->isSubclassOf($class),
                typhoon: static fn(): bool => $typhoon->isSubclassOf($class),
                messagePrefix: "class.isSubclassOf({$class})",
            );
        }
        self::assertSame($native->isTrait(), $typhoon->isTrait(), 'class.isTrait()');
        self::assertSame($native->isUserDefined(), $typhoon->isUserDefined(), 'class.isUserDefined()');
        if ($native->isInstantiable()) {
            // self::assertEquals($native->newInstance(), $typhoon->newInstance(), 'class.newInstance()');
            // self::assertEquals($native->newInstanceArgs(), $typhoon->newInstanceArgs(), 'class.newInstanceArgs()');
            self::assertEquals($native->newInstanceWithoutConstructor(), $typhoon->newInstanceWithoutConstructor(), 'class.newInstanceWithoutConstructor()');
        }
        // TODO setStaticPropertyValue()

        // CONSTANTS

        self::assertSame($native->getConstants(), $typhoon->getConstants(), 'class.getConstants().name');

        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(), $typhoon->getReflectionConstants(), 'class.getReflectionConstants()');

        foreach ($native->getReflectionConstants() as $nativeConstant) {
            self::assertTrue($typhoon->hasConstant($nativeConstant->name), "class.hasConstant({$nativeConstant->name})");
            self::assertSame($native->getConstant($nativeConstant->name), $typhoon->getConstant($nativeConstant->name), "class.getConstant({$nativeConstant->name})");
            $typhoonConstant = $typhoon->getReflectionConstant($nativeConstant->name);
            self::assertNotFalse($typhoonConstant);
            $this->assertConstantEquals($nativeConstant, $typhoonConstant, "class.getReflectionConstant({$nativeConstant->name})");
        }

        self::assertSame($native->getConstants(0), $typhoon->getConstants(0), 'class.getConstants(0).name');
        self::assertSame($native->getConstants(\ReflectionClassConstant::IS_PUBLIC), $typhoon->getConstants(\ReflectionClassConstant::IS_PUBLIC), 'class.getConstants(IS_PUBLIC).name');
        self::assertSame($native->getConstants(\ReflectionClassConstant::IS_PROTECTED), $typhoon->getConstants(\ReflectionClassConstant::IS_PROTECTED), 'class.getConstants(IS_PROTECTED).name');
        self::assertSame($native->getConstants(\ReflectionClassConstant::IS_PRIVATE), $typhoon->getConstants(\ReflectionClassConstant::IS_PRIVATE), 'class.getConstants(IS_PRIVATE).name');
        self::assertSame($native->getConstants(\ReflectionClassConstant::IS_FINAL), $typhoon->getConstants(\ReflectionClassConstant::IS_FINAL), 'class.getConstants(IS_FINAL).name');

        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(0), $typhoon->getReflectionConstants(0), 'class.getReflectionConstants(0)');
        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(\ReflectionClassConstant::IS_PUBLIC), $typhoon->getReflectionConstants(\ReflectionClassConstant::IS_PUBLIC), 'class.getReflectionConstants(IS_PUBLIC)');
        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(\ReflectionClassConstant::IS_PROTECTED), $typhoon->getReflectionConstants(\ReflectionClassConstant::IS_PROTECTED), 'class.getReflectionConstants(IS_PROTECTED)');
        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(\ReflectionClassConstant::IS_PRIVATE), $typhoon->getReflectionConstants(\ReflectionClassConstant::IS_PRIVATE), 'class.getReflectionConstants(IS_PRIVATE)');
        $this->assertReflectionsEqualNoOrder($native->getReflectionConstants(\ReflectionClassConstant::IS_FINAL), $typhoon->getReflectionConstants(\ReflectionClassConstant::IS_FINAL), 'class.getReflectionConstants(IS_FINAL)');

        // PROPERTIES

        $this->assertReflectionsEqualNoOrder($native->getProperties(), $typhoon->getProperties(), 'class.getProperties()');

        foreach ($native->getProperties() as $nativeProperty) {
            self::assertTrue($typhoon->hasProperty($nativeProperty->name), "class.hasProperty({$nativeProperty->name})");
            $this->assertPropertyEquals($nativeProperty, $typhoon->getProperty($nativeProperty->name), "class.getProperty({$nativeProperty->name})");
        }

        $this->assertReflectionsEqualNoOrder($native->getProperties(0), $typhoon->getProperties(0), 'class.getProperties(0)');
        $this->assertReflectionsEqualNoOrder($native->getProperties(\ReflectionProperty::IS_PUBLIC), $typhoon->getProperties(\ReflectionProperty::IS_PUBLIC), 'class.getProperties(IS_PUBLIC)');
        $this->assertReflectionsEqualNoOrder($native->getProperties(\ReflectionProperty::IS_PROTECTED), $typhoon->getProperties(\ReflectionProperty::IS_PROTECTED), 'class.getProperties(IS_PROTECTED)');
        $this->assertReflectionsEqualNoOrder($native->getProperties(\ReflectionProperty::IS_PRIVATE), $typhoon->getProperties(\ReflectionProperty::IS_PRIVATE), 'class.getProperties(IS_PRIVATE)');
        $this->assertReflectionsEqualNoOrder($native->getProperties(\ReflectionProperty::IS_STATIC), $typhoon->getProperties(\ReflectionProperty::IS_STATIC), 'class.getProperties(IS_STATIC)');
        $this->assertReflectionsEqualNoOrder($native->getProperties(\ReflectionProperty::IS_READONLY), $typhoon->getProperties(\ReflectionProperty::IS_READONLY), 'class.getProperties(IS_READONLY)');

        // METHODS

        $this->assertReflectionsEqualNoOrder($native->getMethods(), $typhoon->getMethods(), 'class.getMethods()');

        foreach ($native->getMethods() as $nativeMethod) {
            self::assertTrue($typhoon->hasMethod($nativeMethod->name), "hasMethod({$nativeMethod->name})");
            $this->assertMethodEquals($nativeMethod, $typhoon->getMethod($nativeMethod->name), "getMethod({$nativeMethod->name})");
        }

        $this->assertReflectionsEqualNoOrder($native->getMethods(0), $typhoon->getMethods(0), 'class.getMethods(0)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_FINAL), $typhoon->getMethods(\ReflectionMethod::IS_FINAL), 'class.getMethods(IS_FINAL)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_ABSTRACT), $typhoon->getMethods(\ReflectionMethod::IS_ABSTRACT), 'class.getMethods(IS_ABSTRACT)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_PUBLIC), $typhoon->getMethods(\ReflectionMethod::IS_PUBLIC), 'class.getMethods(IS_PUBLIC)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_PROTECTED), $typhoon->getMethods(\ReflectionMethod::IS_PROTECTED), 'class.getMethods(IS_PROTECTED)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_PRIVATE), $typhoon->getMethods(\ReflectionMethod::IS_PRIVATE), 'class.getMethods(IS_PRIVATE)');
        $this->assertReflectionsEqualNoOrder($native->getMethods(\ReflectionMethod::IS_STATIC), $typhoon->getMethods(\ReflectionMethod::IS_STATIC), 'class.getMethods(IS_STATIC)');
    }

    private function assertConstantEquals(\ReflectionClassConstant $native, \ReflectionClassConstant $typhoon, string $messagePrefix): void
    {
        self::assertSame($native->class, $typhoon->class, $messagePrefix . '.class');
        self::assertSame($native->name, $typhoon->name, $messagePrefix . '.name');
        self::assertSame($native->__toString(), $typhoon->__toString(), $messagePrefix . '.__toString()');
        self::assertAttributesEqual($native->getAttributes(), $typhoon->getAttributes(), $messagePrefix . '.getAttributes()');
        self::assertSame($native->getDeclaringClass()->name, $typhoon->getDeclaringClass()->name, $messagePrefix . '.getDeclaringClass()');
        self::assertSame($native->getDocComment(), $typhoon->getDocComment(), $messagePrefix . '.getDocComment()');
        self::assertSame($native->getModifiers(), $typhoon->getModifiers(), $messagePrefix . '.getModifiers()');
        self::assertSame($native->getName(), $typhoon->getName(), $messagePrefix . '.getName()');
        if (method_exists(\ReflectionClassConstant::class, 'getType')) {
            $nativeType = $native->getType();
            $typhoonType = $native->getType();
            \assert($nativeType === null || $nativeType instanceof \ReflectionType);
            \assert($typhoonType === null || $typhoonType instanceof \ReflectionType);
            $this->assertTypeEquals($nativeType, $typhoonType, $messagePrefix . '.getType()');
        }
        self::assertEquals($native->getValue(), $typhoon->getValue(), $messagePrefix . '.getValue()');
        if (method_exists(\ReflectionClassConstant::class, 'hasType')) {
            self::assertEquals($native->hasType(), $typhoon->hasType(), $messagePrefix . '.hasType()');
        }
        self::assertSame($native->isEnumCase(), $typhoon->isEnumCase(), $messagePrefix . '.isEnumCase()');
        self::assertSame($native->isFinal(), $typhoon->isFinal(), $messagePrefix . '.isFinal()');
        self::assertSame($native->isPrivate(), $typhoon->isPrivate(), $messagePrefix . '.isPrivate()');
        self::assertSame($native->isProtected(), $typhoon->isProtected(), $messagePrefix . '.isProtected()');
        self::assertSame($native->isPublic(), $typhoon->isPublic(), $messagePrefix . '.isPublic()');
    }

    private function assertPropertyEquals(\ReflectionProperty $native, \ReflectionProperty $typhoon, string $messagePrefix): void
    {
        self::assertSame($native->class, $typhoon->class, $messagePrefix . '.class');
        self::assertSame($native->name, $typhoon->name, $messagePrefix . '.name');
        self::assertSame($native->__toString(), $typhoon->__toString(), $messagePrefix . '.__toString()');
        self::assertAttributesEqual($native->getAttributes(), $typhoon->getAttributes(), $messagePrefix . '.getAttributes()');
        self::assertSame($native->getDeclaringClass()->name, $typhoon->getDeclaringClass()->name, $messagePrefix . '.getDeclaringClass()');
        self::assertSame($native->getDefaultValue(), $typhoon->getDefaultValue(), $messagePrefix . '.getDefaultValue()');
        self::assertSame($native->getDocComment(), $typhoon->getDocComment(), $messagePrefix . '.getDocComment()');
        self::assertSame($native->getModifiers(), $typhoon->getModifiers(), $messagePrefix . '.getModifiers()');
        self::assertSame($native->getName(), $typhoon->getName(), $messagePrefix . '.getName()');
        $this->assertTypeEquals($native->getType(), $typhoon->getType(), $messagePrefix . '.getType()');
        // TODO getValue()
        self::assertSame($native->hasDefaultValue(), $typhoon->hasDefaultValue(), $messagePrefix . '.hasDefaultValue()');
        self::assertSame($native->hasType(), $typhoon->hasType(), $messagePrefix . '.hasType()');
        self::assertSame($native->isDefault(), $typhoon->isDefault(), $messagePrefix . '.isDefault()');
        // TODO isInitialized()
        self::assertSame($native->isPrivate(), $typhoon->isPrivate(), $messagePrefix . '.isPrivate()');
        self::assertSame($native->isPromoted(), $typhoon->isPromoted(), $messagePrefix . '.isPromoted()');
        self::assertSame($native->isProtected(), $typhoon->isProtected(), $messagePrefix . '.isProtected()');
        self::assertSame($native->isPublic(), $typhoon->isPublic(), $messagePrefix . '.isPublic()');
        self::assertSame($native->isReadOnly(), $typhoon->isReadOnly(), $messagePrefix . '.isReadOnly()');
        self::assertSame($native->isStatic(), $typhoon->isStatic(), $messagePrefix . '.isStatic()');
        // TODO setValue()
    }

    private function assertMethodEquals(\ReflectionMethod $native, \ReflectionMethod $typhoon, string $messagePrefix): void
    {
        self::assertSame($native->class, $typhoon->class, $messagePrefix . '.class');
        self::assertSame($native->name, $typhoon->name, $messagePrefix . '.name');
        self::assertSame($native->__toString(), $typhoon->__toString(), $messagePrefix . '.__toString()');
        self::assertAttributesEqual($native->getAttributes(), $typhoon->getAttributes(), $messagePrefix . '.getAttributes()');
        if ($native->isStatic()) {
            $this->assertMethodClosureEquals($native->getClosure(), $typhoon->getClosure(), $messagePrefix . '.getClosure()');
        } elseif ($this->canCreateMockObject($native->getDeclaringClass())) {
            $object = $this->createMockObject($native->getDeclaringClass());
            $this->assertMethodClosureEquals($native->getClosure($object), $typhoon->getClosure($object), $messagePrefix . '.getClosure($object)');
        }
        self::assertSame($native->getClosureCalledClass(), $typhoon->getClosureCalledClass(), $messagePrefix . '.getClosureCalledClass()');
        self::assertSame($native->getClosureScopeClass(), $typhoon->getClosureScopeClass(), $messagePrefix . '.getClosureScopeClass()');
        self::assertSame($native->getClosureThis(), $typhoon->getClosureThis(), $messagePrefix . '.getClosureThis()');
        self::assertSame($native->getClosureUsedVariables(), $typhoon->getClosureUsedVariables(), $messagePrefix . '.getClosureUsedVariables()');
        self::assertSame($native->getDeclaringClass()->name, $typhoon->getDeclaringClass()->name, $messagePrefix . '.getDeclaringClass()');
        self::assertSame($native->getDocComment(), $typhoon->getDocComment(), $messagePrefix . '.getDocComment()');
        self::assertSame($native->getEndLine(), $typhoon->getEndLine(), $messagePrefix . '.getEndLine()');
        self::assertEquals($native->getExtension(), $typhoon->getExtension(), $messagePrefix . '.getExtension()');
        self::assertSame($native->getExtensionName(), $typhoon->getExtensionName(), $messagePrefix . '.getExtensionName()');
        self::assertSame($native->getFileName(), $typhoon->getFileName(), $messagePrefix . '.getFileName()');
        self::assertSame($native->getModifiers(), $typhoon->getModifiers(), $messagePrefix . '.getModifiers()');
        self::assertSame($native->getName(), $typhoon->getName(), $messagePrefix . '.getName()');
        self::assertSame($native->getNamespaceName(), $typhoon->getNamespaceName(), $messagePrefix . '.getNamespaceName()');
        self::assertSame($native->getNumberOfParameters(), $typhoon->getNumberOfParameters(), $messagePrefix . '.getNumberOfParameters()');
        self::assertSame($native->getNumberOfRequiredParameters(), $typhoon->getNumberOfRequiredParameters(), $messagePrefix . '.getNumberOfRequiredParameters()');
        $this->assertParametersEqual($native->getParameters(), $typhoon->getParameters(), $messagePrefix . '.getParameters()');
        $this->assertResultOrExceptionEqual(
            native: static fn(): string => $native->getPrototype()->class,
            typhoon: static fn(): string => $typhoon->getPrototype()->class,
            messagePrefix: $messagePrefix . '.getPrototype().class',
        );
        $this->assertResultOrExceptionEqual(
            native: static fn(): string => $native->getPrototype()->name,
            typhoon: static fn(): string => $typhoon->getPrototype()->name,
            messagePrefix: $messagePrefix . '.getPrototype().name',
        );
        self::assertSame($native->getShortName(), $typhoon->getShortName(), $messagePrefix . '.getShortName()');
        // TODO self::assertSame($native->getStartLine(), $typhoon->getStartLine(), $messagePrefix . '.getStartLine()');
        self::assertSame($native->getStaticVariables(), $typhoon->getStaticVariables(), $messagePrefix . '.getStaticVariables()');
        $this->assertTypeEquals($native->getReturnType(), $typhoon->getReturnType(), $messagePrefix . '.getReturnType()');
        $this->assertTypeEquals($native->getTentativeReturnType(), $typhoon->getTentativeReturnType(), $messagePrefix . '.getTentativeReturnType()');
        if (method_exists(\ReflectionMethod::class, 'hasPrototype')) {
            /** @psalm-suppress MixedArgument, UnusedPsalmSuppress */
            self::assertSame($native->hasPrototype(), $typhoon->hasPrototype(), $messagePrefix . '.hasPrototype()');
        }
        self::assertSame($native->hasReturnType(), $typhoon->hasReturnType(), $messagePrefix . '.hasReturnType()');
        self::assertSame($native->hasTentativeReturnType(), $typhoon->hasTentativeReturnType(), $messagePrefix . '.hasTentativeReturnType()');
        self::assertSame($native->inNamespace(), $typhoon->inNamespace(), $messagePrefix . '.inNamespace()');
        // TODO invoke()
        // TODO invokeArgs()
        self::assertSame($native->isAbstract(), $typhoon->isAbstract(), $messagePrefix . '.isAbstract()');
        self::assertSame($native->isClosure(), $typhoon->isClosure(), $messagePrefix . '.isClosure()');
        self::assertSame($native->isConstructor(), $typhoon->isConstructor(), $messagePrefix . '.isConstructor()');
        self::assertSame($native->isDeprecated(), $typhoon->isDeprecated(), $messagePrefix . '.isDeprecated()');
        self::assertSame($native->isDestructor(), $typhoon->isDestructor(), $messagePrefix . '.isDestructor()');
        self::assertSame($native->isFinal(), $typhoon->isFinal(), $messagePrefix . '.isFinal()');
        self::assertSame($native->isGenerator(), $typhoon->isGenerator(), $messagePrefix . '.isGenerator()');
        self::assertSame($native->isInternal(), $typhoon->isInternal(), $messagePrefix . '.isInternal()');
        self::assertSame($native->isPrivate(), $typhoon->isPrivate(), $messagePrefix . '.isPrivate()');
        self::assertSame($native->isProtected(), $typhoon->isProtected(), $messagePrefix . '.isProtected()');
        self::assertSame($native->isPublic(), $typhoon->isPublic(), $messagePrefix . '.isPublic()');
        self::assertSame($native->isStatic(), $typhoon->isStatic(), $messagePrefix . '.isStatic()');
        self::assertSame($native->isUserDefined(), $typhoon->isUserDefined(), $messagePrefix . '.isUserDefined()');
        self::assertSame($native->isVariadic(), $typhoon->isVariadic(), $messagePrefix . '.isVariadic()');
        self::assertSame($native->returnsReference(), $typhoon->returnsReference(), $messagePrefix . '.returnsReference()');
    }

    /**
     * @param array<\ReflectionParameter> $native
     * @param array<\ReflectionParameter> $typhoon
     */
    private function assertParametersEqual(array $native, array $typhoon, string $messagePrefix, bool $assertType = true): void
    {
        $this->assertReflectionsEqual($native, $typhoon, $messagePrefix);

        foreach ($native as $index => $parameter) {
            $this->assertParameterEquals($parameter, $typhoon[$index], $messagePrefix . ".getParameter()[{$index} ({$parameter->name})]", $assertType);
        }
    }

    private function assertParameterEquals(\ReflectionParameter $native, \ReflectionParameter $typhoon, string $messagePrefix, bool $assertType = true): void
    {
        self::assertSame($native->name, $typhoon->name, $messagePrefix . '.name');
        self::assertSame($native->__toString(), $typhoon->__toString(), $messagePrefix . '.__toString()');
        self::assertSame($native->allowsNull(), $typhoon->allowsNull(), $messagePrefix . '.allowsNull()');
        self::assertSame($native->canBePassedByValue(), $typhoon->canBePassedByValue(), $messagePrefix . '.canBePassedByValue()');
        self::assertAttributesEqual($native->getAttributes(), $typhoon->getAttributes(), $messagePrefix . '.getAttributes()');
        self::assertSame($native->getClass()?->name, $typhoon->getClass()?->name, $messagePrefix . '.getClass().name');
        self::assertSame($this->reflectionToString($native->getDeclaringFunction()), $this->reflectionToString($typhoon->getDeclaringFunction()), $messagePrefix . '.getDeclaringFunction()');
        self::assertSame($native->getDeclaringClass()?->name, $typhoon->getDeclaringClass()?->name, $messagePrefix . '.getDeclaringClass().name');
        if ($native->isDefaultValueAvailable()) {
            self::assertSame($native->getDefaultValueConstantName(), $typhoon->getDefaultValueConstantName(), $messagePrefix . '.getDefaultValueConstantName()');
        }
        self::assertSame($native->getName(), $typhoon->getName(), $messagePrefix . '.getName()');
        self::assertSame($native->getPosition(), $typhoon->getPosition(), $messagePrefix . '.getPosition()');
        if ($assertType) {
            $this->assertTypeEquals($native->getType(), $typhoon->getType(), $messagePrefix . '.getType()');
        }
        self::assertSame($native->hasType(), $typhoon->hasType(), $messagePrefix . '.hasType()');
        self::assertSame($native->isArray(), $typhoon->isArray(), $messagePrefix . '.isArray()');
        self::assertSame($native->isCallable(), $typhoon->isCallable(), $messagePrefix . '.isCallable()');
        self::assertSame($native->isDefaultValueAvailable(), $typhoon->isDefaultValueAvailable(), $messagePrefix . '.isDefaultValueAvailable()');
        if ($native->isDefaultValueAvailable()) {
            self::assertEquals($native->getDefaultValue(), $typhoon->getDefaultValue(), $messagePrefix . '.getDefaultValue()');
            self::assertSame($native->isDefaultValueConstant(), $typhoon->isDefaultValueConstant(), $messagePrefix . '.isDefaultValueConstant()');
        }
        self::assertSame($native->isOptional(), $typhoon->isOptional(), $messagePrefix . '.isOptional()');
        self::assertSame($native->isPassedByReference(), $typhoon->isPassedByReference(), $messagePrefix . '.isPassedByReference()');
        self::assertSame($native->isPromoted(), $typhoon->isPromoted(), $messagePrefix . '.isPromoted()');
        self::assertSame($native->isVariadic(), $typhoon->isVariadic(), $messagePrefix . '.isVariadic()');
    }

    /**
     * @param array<\ReflectionAttribute> $native
     * @param array<\ReflectionAttribute> $typhoon
     */
    private function assertAttributesEqual(array $native, array $typhoon, string $messagePrefix): void
    {
        self::assertCount(\count($native), $typhoon, $messagePrefix . '.count');

        foreach ($native as $index => $nativeAttr) {
            self::assertArrayHasKey($index, $typhoon);
            $typhoonAttr = $typhoon[$index];
            self::assertSame($nativeAttr->__toString(), $typhoonAttr->__toString(), $messagePrefix . '.__toString()');
            self::assertEquals($nativeAttr->getArguments(), $typhoonAttr->getArguments(), $messagePrefix . '.getArguments()');
            self::assertSame($nativeAttr->getName(), $typhoonAttr->getName(), $messagePrefix . '.getName()');
            self::assertSame($nativeAttr->getTarget(), $typhoonAttr->getTarget(), $messagePrefix . '.getTarget()');
            self::assertSame($nativeAttr->isRepeated(), $typhoonAttr->isRepeated(), $messagePrefix . '.isRepeated()');
            self::assertEquals($nativeAttr->newInstance(), $typhoonAttr->newInstance(), $messagePrefix . '.newInstance()');
        }
    }

    private function assertMethodClosureEquals(\Closure $native, \Closure $typhoon, string $messagePrefix): void
    {
        $nativeReflection = new \ReflectionFunction($native);
        $typhoonReflection = new \ReflectionFunction($typhoon);

        self::assertSame($nativeReflection->isStatic(), $typhoonReflection->isStatic(), $messagePrefix . '.isStatic()');
        self::assertSame($nativeReflection->getClosureCalledClass()?->name, $typhoonReflection->getClosureCalledClass()?->name, $messagePrefix . '.getClosureCalledClass()');
        self::assertSame($nativeReflection->getClosureScopeClass()?->name, $typhoonReflection->getClosureScopeClass()?->name, $messagePrefix . '.getClosureCalledClass()');
        self::assertSame($nativeReflection->getClosureThis(), $typhoonReflection->getClosureThis(), $messagePrefix . '.getClosureThis()');
        // TODO remove assertType when functions ready
        $this->assertParametersEqual($nativeReflection->getParameters(), $typhoonReflection->getParameters(), $messagePrefix . '.getParameters()', assertType: false);
    }

    /**
     * @param array<\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter> $nativeReflections
     * @param array<\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter> $typhoonReflections
     */
    private function assertReflectionsEqual(array $nativeReflections, array $typhoonReflections, string $message): void
    {
        self::assertSame(
            array_map($this->reflectionToString(...), $nativeReflections),
            array_map($this->reflectionToString(...), $typhoonReflections),
            $message,
        );
    }

    /**
     * @param array<\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter> $nativeReflections
     * @param array<\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter> $typhoonReflections
     */
    private function assertReflectionsEqualNoOrder(array $nativeReflections, array $typhoonReflections, string $message): void
    {
        $nativeReflectionStrings = array_map($this->reflectionToString(...), $nativeReflections);
        sort($nativeReflectionStrings);
        $typhoonReflectionStrings = array_map($this->reflectionToString(...), $typhoonReflections);
        sort($typhoonReflectionStrings);

        self::assertSame($nativeReflectionStrings, $typhoonReflectionStrings, $message);
    }

    /**
     * @return non-empty-string
     */
    private function reflectionToString(\ReflectionFunctionAbstract|\ReflectionClass|\ReflectionClassConstant|\ReflectionProperty|\ReflectionParameter $reflection): string
    {
        return Id::fromReflection($reflection)->toString();
    }

    private function assertTypeEquals(?\ReflectionType $native, ?\ReflectionType $typhoon, string $messagePrefix): void
    {
        self::assertSame($this->normalizeType($native), $this->normalizeType($typhoon), $messagePrefix);
    }

    /**
     * @return ($type is null ? null : array)
     */
    private function normalizeType(?\ReflectionType $type): ?array
    {
        if ($type === null) {
            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            return [
                'type' => 'named',
                'getName' => $type->getName(),
                'isBuiltin' => $type->isBuiltin(),
                'allowsNull' => $type->allowsNull(),
            ];
        }

        \assert($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType);

        $normalizedTypes = array_map($this->normalizeType(...), $type->getTypes());
        sort($normalizedTypes);

        return [
            'type' => $type instanceof \ReflectionUnionType ? 'union' : 'intersection',
            'types' => $normalizedTypes,
            'allowsNull' => $type->allowsNull(),
        ];
    }

    private function canCreateMockObject(\ReflectionClass $class): bool
    {
        if (method_exists($class, 'isReadonly') && $class->isReadonly()) {
            return false;
        }

        if ($class->isTrait()) {
            return false;
        }

        if ($class->isEnum()) {
            /** @psalm-suppress MixedMethodCall */
            return $class->name::cases() !== [];
        }

        return !\in_array($class->name, [
            \UnitEnum::class,
            \BackedEnum::class,
            \Throwable::class,
            \Traversable::class,
        ], true);
    }

    /**
     * @return \Generator<int, class-string>
     * @psalm-suppress MoreSpecificReturnType
     */
    private function getClasses(\ReflectionClass $class): \Generator
    {
        yield '';
        yield 'HELLO!';
        yield $class->name;
        yield from $class->getInterfaceNames();
        $parent = $class->getParentClass();

        while ($parent !== false) {
            yield $parent->name;
            $parent = $parent->getParentClass();
        }

        yield \Iterator::class;
        yield \ArrayAccess::class;
        yield \Throwable::class;
        yield \UnitEnum::class;
        yield Variance::class;
        yield \FilterIterator::class;
        yield \stdClass::class;
        yield Trait1::class;
    }

    /**
     * @template T of object
     * @param \ReflectionClass<T> $class
     * @return T
     */
    private function createMockObject(\ReflectionClass $class): object
    {
        if ($class->isAbstract() || $class->isInterface()) {
            /** @var T */
            return \Mockery::mock($class->name);
        }

        if ($class->isEnum()) {
            /**
             * @var list<T>
             * @psalm-suppress MixedMethodCall
             */
            $cases = $class->name::cases();

            if ($cases === []) {
                throw new \LogicException(sprintf('Enum %s has no cases.', $class->name));
            }

            return $cases[0];
        }

        return $class->newInstanceWithoutConstructor();
    }

    private function assertResultOrExceptionEqual(\Closure $native, \Closure $typhoon, string $messagePrefix): void
    {
        $nativeException = null;
        $nativeResult = null;
        $typhoonException = null;
        $typhoonResult = null;

        try {
            $nativeResult = $native();
        } catch (\ReflectionException $nativeException) {
        }

        try {
            $typhoonResult = $typhoon();
        } catch (\ReflectionException $typhoonException) {
        }

        if ($nativeException !== null) {
            $messagePrefix .= '.exception';
            self::assertInstanceOf($nativeException::class, $typhoonException, $messagePrefix . '.class');
            self::assertSame($nativeException->getMessage(), $typhoonException->getMessage(), $messagePrefix . '.getMessage()');
            self::assertEquals($nativeException->getPrevious(), $typhoonException->getPrevious(), $messagePrefix . '.getPrevious()');
            self::assertSame($nativeException->getCode(), $typhoonException->getCode(), $messagePrefix . '.getCode()');

            return;
        }

        self::assertNull($typhoonException, $messagePrefix);
        self::assertSame($nativeResult, $typhoonResult, $messagePrefix);
    }
}
