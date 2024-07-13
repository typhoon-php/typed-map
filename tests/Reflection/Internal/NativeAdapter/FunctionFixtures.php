<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\NativeAdapter;

final class FunctionFixtures
{
    private function __construct() {}

    /**
     * @var ?array<string, array{callable-string}>
     */
    private static ?array $functions = null;

    /**
     * @return array<string, array{callable-string}>
     */
    public static function get(): array
    {
        if (self::$functions !== null) {
            return self::$functions;
        }

        $functions = [
            'time',
            'trim',
            ...self::loadFromFile(__DIR__ . '/Fixtures/functions.php'),
        ];

        if (\PHP_VERSION_ID >= 80200) {
            $functions = [...$functions, ...self::loadFromFile(__DIR__ . '/Fixtures/functions_php82.php')];
        }

        if (\PHP_VERSION_ID >= 80300) {
            $functions = [...$functions, ...self::loadFromFile(__DIR__ . '/Fixtures/functions_php83.php')];
        }

        self::$functions = [];

        foreach ($functions as $function) {
            self::$functions[$function] = [$function];
        }

        return self::$functions;
    }

    /**
     * @param non-empty-string $file
     * @return array<callable-string>
     */
    private static function loadFromFile(string $file): array
    {
        $declared = get_defined_functions()['user'];
        /** @psalm-suppress UnresolvableInclude */
        require_once $file;

        return array_diff(get_defined_functions()['user'], $declared);
    }
}
