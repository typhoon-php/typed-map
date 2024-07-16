# Typhoon Types Interoperability

## PHP Native Types

| PHPStan           | Psalm       | Typhoon                                |
|-------------------|-------------|----------------------------------------|
| `null`            | `null`      | `types::null`                          |
| `void`            | `void`      | `types::void`                          |
| `never`           | `never`     | `types::never`                         |
| `true`            | `true`      | `types::true`, `types::scalar(true)`   |
| `false`           | `false`     | `types::false`, `types::scalar(false)` |
| `bool`, `boolean` | `bool`      | `types::bool`                          |
| `int`, `integer`  | `int`       | `types::int`                           |
| `float`, `double` | `float`     | `types::float`                         |
| `string`          | `string`    | `types::string`                        |
| `scalar`          | `scalar`    | `types::scalar`                        |
| `numeric`         | `numeric`   | `types::numeric`                       |
| `resource`        | `resource`  | `types::resource`                      |
| `array`           | `array`     | `types::array`                         |
| `iterable`        | `iterable`  | `types::iterable`                      |
| `object`          | `object`    | `types::object`                        |
| `Foo`             | `Foo`       | `types::object(Foo::class)`            |
| `Closure`         | `Closure`   | `types::closure`                       |
| `Generator`       | `Generator` | `types::generator()`                   |
| `self`            | `self`      | `types::self()`                        |
| `parent`          | `parent`    | `types::parent()`                      |
| `static`          | `static`    | `types::static()`                      |
| `callable`        | `callable`  | `types::callable`                      |
| `mixed`           | `mixed`     | `types::mixed`                         |

## Advanced Integers

| PHPStan                   | Psalm                        | Typhoon                                                                                                                       |
|---------------------------|------------------------------|-------------------------------------------------------------------------------------------------------------------------------|
| `literal-int`             | `literal-int`                | `types::literalInt`                                                                                                           |
| `123` (int literal)       | `123`                        | `types::int(123)`, `types::scalar(123)`                                                                                       |
| `positive-int`            | `positive-int`               | `types::positiveInt`                                                                                                          |
| `negative-int`            | `negative-int`               | `types::negativeInt`                                                                                                          |
| `non-positive-int`        | `non-positive-int`           | `types::nonPositiveInt`                                                                                                       |
| `non-negative-int`        | `non-negative-int`           | `types::nonNegativeInt`                                                                                                       |
| `non-zero-int`            | `negative-int\|positive-int` | `types::nonZeroInt`                                                                                                           |
| `int<-5, 6>`              | `int<-5, 6>`                 | `types::intRange(-5, 6)`                                                                                                      |
| `int<min, 6>`             | `int<min, 6>`                | `types::intRange(max: 6)`                                                                                                     |
| `int<-5, max>`            | `int<-5, max>`               | `types::intRange(min: -5)`                                                                                                    |
| `int-mask<1, 2, 4>`       | `int-mask<1, 2, 4>`          | `types::intMask(1, 2, 4)`                                                                                                     |
| `int-mask-of<Foo::INT_*>` | `int-mask-of<Foo::INT_*>`    | `types::intMaskOf(types::classConstant(Foo::class, 'INT_*')`, `types::intMaskOf(types::classConstantMask(Foo::class, 'INT_')` |

## Advanced Floats

| PHPStan                | Psalm  | Typhoon                                   |
|------------------------|--------|-------------------------------------------|
|                        |        | `types::literalFloat`                     |
| `12.5` (float literal) | `12.5` | `types::int(12.5)`, `types::scalar(12.5)` |

## Advanced Strings

| PHPStan                             | Psalm                               | Typhoon                                                                                               |
|-------------------------------------|-------------------------------------|-------------------------------------------------------------------------------------------------------|
| `non-empty-string`                  | `non-empty-string`                  | `types::nonEmptyString`                                                                               |
| `literal-string`                    | `literal-string`                    | `types::literalString`                                                                                |
| `'abc'` (string literal)            | `'abc'`                             | `types::string('abc')`, `types::scalar('abc')`                                                        |
| `truthy-string`, `non-falsy-string` | `truthy-string`, `non-falsy-string` | `types::truthyString`, `types::nonFalsyString`                                                        |
| `numeric-string`                    | `numeric-string`                    | `types::numericString`                                                                                |
| `callable-string`                   | `callable-string`                   | `types::callableString`                                                                               |
|                                     | `lowercase-string`                  | `types::lowercaseString`                                                                              |
|                                     | `non-empty-lowercase-string`        | `types::intersection(types::nonEmptyString, types::lowercaseString)`                                  |
| `Foo::class`                        | `Foo::class`                        | `types::classConstant(Foo::class, 'class')`, `types::string(Foo::class)`, `types::scalar(Foo::class)` |
| `class-string`                      | `class-string`                      | `types::classString`                                                                                  |
|                                     | `interface-string`                  |                                                                                                       |
|                                     | `trait-string`                      |                                                                                                       |
|                                     | `enum-string`                       |                                                                                                       |
| `class-string<Foo>`                 | `class-string<Foo>`                 | `types::classString(types::object(Foo::class))`                                                       |

## Advanced Arrays

| PHPStan                                                   | Psalm                                                     | Typhoon                                                                                                              |
|-----------------------------------------------------------|-----------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `Foo[]`                                                   | `Foo[]`                                                   | `types::array(value: types::object(Foo::class))`                                                                     |
| `list{}`, `list<never>`, `array{}`, `array<never, never>` | `list{}`, `list<never>`, `array{}`, `array<never, never>` | `types::list(types::never)`, `types::listShape()`, `types::array(types::never, types::never)`, `types::arrayShape()` |
| `list<string>`                                            | `list<string>`                                            | `types::list(types::string)`                                                                                         |
| `non-empty-list<string>`                                  | `non-empty-list<string>`                                  | `types::nonEmptyList(types::string)`                                                                                 |
| `list{int, string}`                                       | `list{int, string}`                                       | `types::listShape([types::int, types::string])`                                                                      |
| `list{int, 1?: string}`                                   | `list{int, 1?: string}`                                   | `types::listShape([types::int, types::optional(types::string)])`                                                     |
| `list{int, ...}`                                          | `list{int, ...}`                                          | `types::unsealedListShape([types::int])`                                                                             |
| https://github.com/phpstan/phpdoc-parser/issues/245       | `list{int, ...<string>}`                                  | `types::unsealedListShape([types::int], types::string)`                                                              |
| `array<string>`                                           | `array<string>`                                           | `types::array(value: types::string)`                                                                                 |
| `array<int, string>`                                      | `array<int, string>`                                      | `types::array(types::int, types::string)`                                                                            |
| `non-empty-array<array-key, string>`                      | `non-empty-array<array-key, string>`                      | `types::nonEmptyArray(types::arrayKey, types::string)`                                                               |
| `array{int, string}`                                      | `array{int, string}`                                      | `types::arrayShape([types::int, types::string])`                                                                     |
| `array{int, a?: string}`                                  | `array{int, a?: string}`                                  | `types::arrayShape([types::int, 'a' => types::optional(types::string)])`                                             |
| `array{int, ...}`                                         | `array{int, ...}`                                         | `types::unsealedArrayShape([types::int])`                                                                            |
| https://github.com/phpstan/phpdoc-parser/issues/245       | `array{float, ...<int, string>}`                          | `types::unsealedArrayShape([types::float], types::int, types::string)`                                               |

## Other

| PHPStan                  | Psalm             | Typhoon                          |
|--------------------------|-------------------|----------------------------------|
| `array-key`              | `array-key`       | `types::arrayKey`                |
| `open-resource`          | `open-resource`   |                                  |
| `closed-resource`        | `closed-resource` |                                  |
| `pure-callable`          | `pure-callable`   |                                  |
| `PHP_INT_MAX` (constant) |                   | `types::constant('PHP_INT_MAX')` |
