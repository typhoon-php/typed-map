<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\NativeAdapter;

final class ClassFixtures
{
    private function __construct() {}

    /**
     * @var ?array<string, array{class-string}>
     */
    private static ?array $classes = null;

    /**
     * @return array<string, array{class-string}>
     */
    public static function get(): array
    {
        if (self::$classes !== null) {
            return self::$classes;
        }

        $classes = [
            \Traversable::class,
            \Iterator::class,
            \IteratorAggregate::class,
            \Stringable::class,
            \UnitEnum::class,
            \BackedEnum::class,
            \Countable::class,
            \Serializable::class,
            \ArrayAccess::class,
            \Throwable::class,
            // \Error::class,
            // \Exception::class,
            \ArrayObject::class,
            ...self::loadFromFile(__DIR__ . '/Fixtures/classes.php'),
        ];

        if (\PHP_VERSION_ID >= 80200) {
            $classes = [...$classes, ...self::loadFromFile(__DIR__ . '/Fixtures/classes_php82.php')];
        }

        if (\PHP_VERSION_ID >= 80300) {
            $classes = [...$classes, ...self::loadFromFile(__DIR__ . '/Fixtures/classes_php83.php')];
        }

        self::$classes = [];

        foreach ($classes as $class) {
            self::$classes[str_replace("\0" . __DIR__, '', $class)] = [$class];
        }

        return self::$classes;
    }

    /**
     * @param non-empty-string $file
     * @return array<class-string>
     */
    private static function loadFromFile(string $file): array
    {
        $declared = self::allDeclaredClasses();
        /** @psalm-suppress UnresolvableInclude */
        require_once $file;

        return array_diff(self::allDeclaredClasses(), $declared);
    }

    /**
     * @return list<class-string>
     */
    private static function allDeclaredClasses(): array
    {
        return [
            ...get_declared_classes(),
            ...get_declared_interfaces(),
            ...get_declared_traits(),
        ];
    }
}
