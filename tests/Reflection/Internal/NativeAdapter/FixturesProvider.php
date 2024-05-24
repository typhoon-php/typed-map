<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\NativeAdapter;

final class FixturesProvider
{
    /**
     * @var ?array<string, array{class-string}>
     */
    private static ?array $classes = null;

    private function __construct() {}

    /**
     * @return array<string, array{class-string}>
     */
    public static function classes(): array
    {
        if (self::$classes !== null) {
            return self::$classes;
        }

        self::$classes = [
            \Traversable::class => [\Traversable::class],
            \Iterator::class => [\Iterator::class],
            \IteratorAggregate::class => [\IteratorAggregate::class],
            \Stringable::class => [\Stringable::class],
            \UnitEnum::class => [\UnitEnum::class],
            \BackedEnum::class => [\BackedEnum::class],
            ...self::loadFromFile(__DIR__ . '/Fixtures/classes.php'),
        ];

        if (\PHP_VERSION_ID >= 80200) {
            self::$classes = [
                ...self::$classes,
                ...self::loadFromFile(__DIR__ . '/Fixtures/classes_php82.php'),
            ];
        }

        if (\PHP_VERSION_ID >= 80300) {
            self::$classes = [
                ...self::$classes,
                ...self::loadFromFile(__DIR__ . '/Fixtures/classes_php83.php'),
            ];
        }

        return self::$classes;
    }

    /**
     * @param non-empty-string $file
     * @return array<string, array{class-string}>
     */
    private static function loadFromFile(string $file): array
    {
        $classes = [];

        $declaredClasses = self::allDeclaredClasses();

        /** @psalm-suppress UnresolvableInclude */
        require_once $file;

        foreach (array_diff(self::allDeclaredClasses(), $declaredClasses) as $class) {
            $classes[str_replace("\0" . __DIR__, '', $class)] = [$class];
        }

        return $classes;
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
