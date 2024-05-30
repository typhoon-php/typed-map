<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\NativeAdapter;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class AdapterFullnessTest extends TestCase
{
    /**
     * @return \Generator<class-string, array{class-string}>
     */
    public static function adapterClasses(): \Generator
    {
        yield AttributeAdapter::class => [AttributeAdapter::class];
        yield ClassConstantAdapter::class => [ClassConstantAdapter::class];
        yield ClassAdapter::class => [ClassAdapter::class];
        yield MethodAdapter::class => [MethodAdapter::class];
        yield ParameterAdapter::class => [ParameterAdapter::class];
        yield PropertyAdapter::class => [PropertyAdapter::class];
        yield NamedTypeAdapter::class => [NamedTypeAdapter::class];
        yield UnionTypeAdapter::class => [UnionTypeAdapter::class];
        yield IntersectionTypeAdapter::class => [IntersectionTypeAdapter::class];
    }

    #[DataProvider('adapterClasses')]
    public function testAllMethodsImplemented(string $class): void
    {
        foreach ((new \ReflectionClass($class))->getMethods() as $method) {
            self::assertSame(
                $class,
                $method->class,
                sprintf('Method %s::%s() is not overridden in %s', $method->class, $method->name, $class),
            );
        }
    }
}
