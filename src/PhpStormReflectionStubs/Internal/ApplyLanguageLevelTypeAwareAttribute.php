<?php

declare(strict_types=1);

namespace Typhoon\PhpStormReflectionStubs\Internal;

use Typhoon\DeclarationId\AnonymousClassId;
use Typhoon\DeclarationId\AnonymousFunctionId;
use Typhoon\DeclarationId\NamedClassId;
use Typhoon\DeclarationId\NamedFunctionId;
use Typhoon\Reflection\Internal\ClassHook;
use Typhoon\Reflection\Internal\Data;
use Typhoon\Reflection\Internal\FunctionHook;
use Typhoon\Reflection\Internal\Reflector;
use Typhoon\Reflection\Internal\TypedMap\TypedMap;
use Typhoon\Type\Type;
use Typhoon\Type\types;

/**
 * @internal
 * @psalm-internal Typhoon\PhpStormReflectionStubs
 */
enum ApplyLanguageLevelTypeAwareAttribute implements FunctionHook, ClassHook
{
    case Instance;
    private const ATTRIBUTE = 'JetBrains\PhpStorm\Internal\LanguageLevelTypeAware';

    public function process(NamedFunctionId|AnonymousFunctionId|NamedClassId|AnonymousClassId $id, TypedMap $data, Reflector $reflector): TypedMap
    {
        return $data
            ->with(Data::Parameters, array_map(self::apply(...), $data[Data::Parameters]))
            ->with(Data::Constants, array_map(self::apply(...), $data[Data::Constants]))
            ->with(Data::Properties, array_map(self::apply(...), $data[Data::Properties]))
            ->with(Data::Methods, array_map(
                static fn(TypedMap $method): TypedMap => self::apply(
                    $method->with(Data::Parameters, array_map(self::apply(...), $method[Data::Parameters])),
                ),
                $data[Data::Methods],
            ));
    }

    private static function apply(TypedMap $data): TypedMap
    {
        $nativeType = self::getNativeType($data[Data::Attributes]);

        if ($nativeType === null) {
            return $data;
        }

        return $data->with(Data::Type, $data[Data::Type]->withNative($nativeType));
    }

    /**
     * @param list<TypedMap> $attributesData
     */
    private static function getNativeType(array $attributesData): ?Type
    {
        foreach ($attributesData as $attributeData) {
            if ($attributeData[Data::AttributeClassName] !== self::ATTRIBUTE) {
                continue;
            }

            /** @var array{0?: array<string, string>, 1?: string, languageLevelTypeMap?: array<string, string>, default?: string} */
            $arguments = $attributeData[Data::ArgumentsExpression]->evaluate();
            $types = ['' => $arguments[1] ?? $arguments['default'] ?? ''];

            foreach ($arguments[0] ?? $arguments['languageLevelTypeMap'] ?? [] as $version => $type) {
                if (version_compare(PHP_VERSION, $version) >= 0) {
                    $types[$version] = $type;
                }
            }

            ksort($types);

            return self::parseType($types[array_key_last($types)]);
        }

        return null;
    }

    private static function parseType(string $type): Type
    {
        if ($type === '') {
            return types::mixed;
        }

        if (str_contains($type, '&')) {
            return types::intersection(...array_map(
                static fn(string $part): Type => self::parseType(trim($part, '()')),
                explode('&', $type),
            ));
        }

        if (str_contains($type, '|')) {
            return types::union(...array_map(self::parseType(...), explode('|', $type)));
        }

        if ($type[0] === '?') {
            return types::nullable(self::parseType(substr($type, 1)));
        }

        return match ($type) {
            'never' => types::never,
            'void' => types::void,
            'null' => types::null,
            'true' => types::true,
            'false' => types::false,
            'bool' => types::bool,
            'int' => types::int,
            'float' => types::float,
            'string' => types::string,
            'resource' => types::resource,
            'array' => types::array,
            'iterable' => types::iterable,
            'object' => types::object,
            'callable' => types::callable,
            'mixed' => types::mixed,
            default => types::object($type),
        };
    }
}
