# Typhoon Reflection

Typhoon Reflection is an alternative to [native PHP Reflection](https://www.php.net/manual/en/book.reflection.php). It is:
- static (does not run reflected code),
- fast (due to lazy loading and caching),
- [99% compatible with native reflection](#compatibility-with-native-reflection),
- supports most of the Psalm and PHPStan phpDoc types,
- can resolve templates,
- can be safely used with [zend.enable_gc=0](https://www.php.net/manual/en/info.configuration.php#ini.zend.enable-gc).

## Installation

```
composer require typhoon/reflection jetbrains/phpstorm-stubs
```

Installing `jetbrains/phpstorm-stubs` is highly recommended. Without stubs core PHP classes are reflected via
[NativeReflector](../src/Reflection/NativeReflector/NativeReflector.php) that does not support phpDoc types. 

## Basic Usage

```php
use Typhoon\Reflection\TyphoonReflector;
use function Typhoon\TypeStringifier\stringify;

/**
 * @template TTag of non-empty-string
 */
final readonly class Article
{
    /**
     * @param non-empty-list<TTag> $tags
     */
    public function __construct(
        private array $tags,
    ) {}
}

$reflector = TyphoonReflector::build();
$articleTagsType = $reflector->reflectClass(Article::class)->property('tags')?->type();

var_dump(stringify($articleTagsType)); // non-empty-list<TTag@Article>
```

## Caching

By default, Typhoon Reflection uses in-memory LRU cache which should be enough for the majority of use cases.

However, if you need persistent cache, you can use any [PSR-16](https://www.php-fig.org/psr/psr-16/) implementation. We highly recommend [Typhoon OPcache](https://github.com/typhoon-php/opcache).
It stores values as php files that could be opcached. It is much faster than an average file cache implementation that uses `serialize`. 

```php
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\OPcache\TyphoonOPcache;

$reflector = TyphoonReflector::build(
    cache: new TyphoonOPcache('path/to/cache/dir'),
);
```

To detect file changes during development, decorate your cache with [FreshCache](../src/Reflection/Cache/FreshCache.php).

```php
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Reflection\Cache\FreshCache;
use Typhoon\OPcache\TyphoonOPcache;

$reflector = TyphoonReflector::build(
    cache: new FreshCache(new TyphoonOPcache('path/to/cache/dir')),
);
```

## Class locators

By default, reflector uses:
- [ComposerLocator](../src/Reflection/Locator/ComposerLocator.php) if composer autoloading is used, 
- [PhpStormStubsLocator](../src/Reflection/Locator/PhpStormStubsLocator.php) if `jetbrains/phpstorm-stubs` is installed,
- [NativeReflectionFileLocator](../src/Reflection/Locator/NativeReflectionFileLocator.php) (uses native reflection to obtain class file),
- [NativeReflectionLocator](../src/Reflection/Locator/NativeReflectionLocator.php) (returns native reflection itself).

You can implement your own locators and pass them to the `build` method:

```php
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\TyphoonReflector;

final class MyLocator implements Locator
{
    // ...
}

$reflector = TyphoonReflector::build(
    locators: [
        new MyLocator(),
        ...TyphoonReflector::defaultLocators(),
    ],
);
```

## Compatibility with native reflection

| `ReflectionClass`                 | `Typhoon\Reflection\ClassReflection` |
|-----------------------------------|--------------------------------------|
| `IS_READONLY`                     | ✅ Defined for PHP 8.1                |
| `$name`                           | ✅                                    |
| `__construct()`                   | ❌ `@internal`                        |
| `__toString()`                    | ✅ Via native reflection              |
| `getAttributes()`                 | ✅                                    |
| `getConstant()`                   | ✅ Via native reflection              |
| `getConstants()`                  | ✅ Via native reflection              |
| `getConstructor()`                | ✅                                    |
| `getDefaultProperties()`          | ✅ Via native reflection              |
| `getDocComment()`                 | ✅️                                   |
| `getEndLine()`                    | ✅                                    |
| `getExtension()`                  | ✅ Via native reflection              |
| `getExtensionName()`              | ✅                                    |
| `getFileName()`                   | ✅                                    |
| `getInterfaceNames()`             | ✅                                    |
| `getInterfaces()`                 | ✅                                    |
| `getMethods()`                    | ✅                                    |
| `getMethod()`                     | ✅                                    |
| `getModifiers()`                  | ✅                                    |
| `getName()`                       | ✅                                    |
| `getNamespaceName()`              | ✅                                    |
| `getParentClass()`                | ✅                                    |
| `getProperties()`                 | ✅                                    |
| `getProperty()`                   | ✅                                    |
| `getReflectionConstant()`         | ✅                                    |
| `getReflectionConstants()`        | ✅                                    |
| `getShortName()`                  | ✅                                    |
| `getStartLine()`                  | ✅️                                   |
| `getStaticProperties()`           | ✅ Via native reflection              |
| `getStaticPropertyValue()`        | ✅ Via native reflection              |
| `getTraitAliases()`               | ✅                                    |
| `getTraitNames()`                 | ✅                                    |
| `getTraits()`                     | ✅                                    |
| `hasConstant()`                   | ✅                                    |
| `hasMethod()`                     | ✅                                    |
| `hasProperty()`                   | ✅                                    |
| `implementsInterface()`           | ✅                                    |
| `inNamespace()`                   | ✅                                    |
| `isAbstract()`                    | ✅                                    |
| `isAnonymous()`                   | ✅                                    |
| `isCloneable()`                   | ✅                                    |
| `isEnum()`                        | ✅                                    |
| `isFinal()`                       | ✅                                    |
| `isInstance()`                    | ✅                                    |
| `isInstantiable()`                | ✅                                    |
| `isInterface()`                   | ✅                                    |
| `isInternal()`                    | ✅                                    |
| `isIterable()`                    | ✅                                    |
| `isIterateable()`                 | ✅                                    |
| `isReadOnly()`                    | ✅                                    |
| `isSubclassOf()`                  | ✅                                    |
| `isTrait()`                       | ✅                                    |
| `isUserDefined()`                 | ✅                                    |
| `newInstance()`                   | ✅ Via native reflection              |
| `newInstanceArgs()`               | ✅ Via native reflection              |
| `newInstanceWithoutConstructor()` | ✅ Via native reflection              |
| `setStaticPropertyValue()`        | ✅ Via native reflection              | 

| `ReflectionClassConstant` | `Typhoon\Reflection\ClassConstantReflection` |
|---------------------------|----------------------------------------------|
| `$class`                  | ✅                                            |
| `$name`                   | ✅                                            |
| `__construct()`           | ❌ `@internal`                                |
| `__toString()`            | ✅ Via native reflection                      |
| `getAttributes()`         | ✅                                            |
| `getDeclaringClass()`     | ✅                                            |
| `getDocComment()`         | ✅                                            |
| `getModifiers()`          | ✅                                            |
| `getName()`               | ✅                                            |
| `getType()`               | ✅                                            |
| `getValue()`              | ✅ Via native reflection                      |
| `hasType()`               | ✅                                            |
| `isEnumCase()`            | ✅                                            |
| `isFinal()`               | ✅                                            |
| `isPrivate()`             | ✅                                            |
| `isProtected()`           | ✅                                            |
| `isPublic()`              | ✅                                            |

| `ReflectionProperty`  | `Typhoon\Reflection\PropertyReflection` |
|-----------------------|-----------------------------------------|
| `$class`              | ✅                                       |
| `$name`               | ✅                                       |
| `__construct()`       | ❌ `@internal`                           |
| `__toString()`        | ✅ Via native reflection                 |
| `getAttributes()`     | ✅                                       |
| `getDeclaringClass()` | ✅                                       |
| `getDefaultValue()`   | ✅ Via native reflection                 |
| `getDocComment()`     | ✅️                                      |
| `getModifiers()`      | ✅                                       |
| `getName()`           | ✅                                       |
| `getType()`           | ✅                                       |
| `getValue()`          | ✅ Via native reflection                 |
| `hasDefaultValue()`   | ✅                                       |
| `hasType()`           | ✅                                       |
| `isDefault()`         | ✅                                       |
| `isInitialized()`     | ✅ Via native reflection                 |
| `isPrivate()`         | ✅                                       |
| `isPromoted()`        | ✅                                       |
| `isProtected()`       | ✅                                       |
| `isPublic()`          | ✅                                       |
| `isReadOnly()`        | ✅                                       |
| `isStatic()`          | ✅                                       |
| `setAccessible()`     | ✅                                       |
| `setValue()`          | ✅ Via native reflection                 |

| `ReflectionMethod`                       | `Typhoon\Reflection\MethodReflection` |
|------------------------------------------|---------------------------------------|
| `$class`                                 | ✅                                     |
| `$name`                                  | ✅                                     |
| `__construct()`                          | ❌ `@internal`                         |
| `__toString()`                           | ✅ Via native reflection               |
| `createFromMethodName()`                 | ✅                                     |
| `getAttributes()`                        | ✅                                     |
| `getClosure()`                           | ✅ Via native reflection               |
| `getClosureCalledClass()`                | ✅                                     |
| `getClosureScopeClass()`                 | ✅                                     |
| `getClosureThis()`                       | ✅                                     |
| `getClosureUsedVariables()`              | ✅                                     |
| `getDeclaringClass()`                    | ✅                                     |
| `getDocComment()`                        | ✅️                                    |
| `getEndLine()`                           | ✅                                     |
| `getExtension()`                         | ✅ Via native reflection               |
| `getExtensionName()`                     | ✅                                     |
| `getFileName()`                          | ✅️                                    |
| `getModifiers()`                         | ✅                                     |
| `getName()`                              | ✅                                     |
| `getNamespaceName()`                     | ✅                                     |
| `getNumberOfParameters()`                | ✅                                     |
| `getNumberOfRequiredParameters()`        | ✅                                     |
| `getParameters()`                        | ✅                                     |
| `getPrototype()`                         | ✅                                     |
| `getReturnType()`                        | ✅                                     |
| `getShortName()`                         | ✅                                     |
| `getStartLine()`                         | ✅️                                    |
| `getStaticVariables()`                   | ✅️ Via native reflection              |
| `getTentativeReturnType()`               | ✅                                     |
| `hasPrototype()`                         | ✅                                     |
| `hasReturnType()`                        | ✅                                     |
| `hasTentativeReturnType()`               | ✅                                     |
| `inNamespace()`                          | ✅                                     |
| `invoke()`                               | ✅ Via native reflection               |
| `invokeArgs()`                           | ✅ Via native reflection               |
| `isAbstract()`                           | ✅                                     |
| `isClosure()`                            | ✅                                     |
| `isConstructor()`                        | ✅                                     |
| `isDeprecated()`                         | ✅                                     |
| `isDestructor()`                         | ✅                                     |
| `isFinal()`                              | ✅                                     |
| `isGenerator()`                          | ✅                                     |
| `isInternal()`                           | ✅                                     |
| `isPrivate()`                            | ✅                                     |
| `isProtected()`                          | ✅                                     |
| `isPublic()`                             | ✅                                     |
| `isStatic()`                             | ✅                                     |
| `isUserDefined()`                        | ✅                                     |
| `isVariadic()`                           | ✅                                     |
| `returnsReference()`                     | ✅                                     |
| `setAccessible()`                        | ✅                                     |

| `ReflectionParameter`           | `Typhoon\Reflection\ParameterReflection` |
|---------------------------------|------------------------------------------|
| `$name`                         | ✅                                        |
| `__construct()`                 | ❌ `@internal`                            |
| `__toString()`                  | ✅ Via native reflection                  |
| `allowsNull()`                  | ✅                                        |
| `canBePassedByValue()`          | ✅                                        |
| `getAttributes()`               | ✅                                        |
| `getClass()`                    | ✅                                        |
| `getDeclaringClass()`           | ✅                                        |
| `getDeclaringFunction()`        | ✅                                        |
| `getDefaultValue()`             | ✅ Via native reflection                  |
| `getDefaultValueConstantName()` | ✅ Via native reflection                  |
| `getName()`                     | ✅                                        |
| `getPosition()`                 | ✅                                        |
| `getType()`                     | ✅                                        |
| `hasType()`                     | ✅                                        |
| `isArray()`                     | ✅                                        |
| `isCallable()`                  | ✅                                        |
| `isDefaultValueAvailable()`     | ✅                                        |
| `isDefaultValueConstant()`      | ✅ Via native reflection                  |
| `isOptional()`                  | ✅                                        |
| `isPassedByReference()`         | ✅                                        |
| `isPromoted()`                  | ✅                                        |
| `isVariadic()`                  | ✅                                        |

| `ReflectionAttribute` | `Typhoon\Reflection\AttributeReflection` |
|-----------------------|------------------------------------------|
| `__toString()`        | ✅ Via native reflection                  |
| `getArguments()`      | ✅ Via native reflection                  |
| `getName()`           | ✅                                        |
| `getTarget()`         | ✅                                        |
| `isRepeated()`        | ✅                                        |
| `newInstance()`       | ✅ Via native reflection                  |
