# Typhoon Types Interoperability

## Basic

| PHPStan                     | Psalm      | Typhoon           |
|-----------------------------|------------|-------------------|
| `null`                      | `null`     | `types::null`     |
| `void`                      | `void`     | `types::void`     |
| `never`                     | `never`    | `types::never`    |
| `true`                      | `true`     | `types::true`     |
| `false`                     | `false`    | `types::false`    |
| `bool`                      | `bool`     | `types::bool`     |
| `int`                       | `int`      | `types::int`      |
| `float`, `double`           | `float`    | `types::float`    |
| `string`                    | `string`   | `types::string`   |
| `scalar`                    | `scalar`   | `types::scalar`   |
| `resource`                  | `resource` | `types::resource` |
| `array`                     | `array`    | `types::array`    |
| `iterable`                  | `iterable` | `types::iterable` |
| `object`                    | `object`   | `types::object`   |
| `callable`, `pure-callable` | `callable` | `types::callable` |
| `mixed`                     | `mixed`    | `types::mixed`    |

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
| `int-mask<1,2,4>`         | `int-mask<1, 2, 4>`          | `types::intMask(1,2,4)`                                                                                                       |
| `int-mask-of<Foo::INT_*>` | `int-mask-of<Foo::INT_*>`    | `types::intMaskOf(types::classConstant(Foo::class, 'INT_*')`, `types::intMaskOf(types::classConstantMask(Foo::class, 'INT_')` |

## Advanced Floats

| PHPStan                | Psalm  | Typhoon                                   |
|------------------------|--------|-------------------------------------------|
|                        |        | `literal-float`                           |
| `12.5` (float literal) | `12.5` | `types::int(12.5)`, `types::scalar(12.5)` |

## Advanced Strings

| PHPStan                             | Psalm                               | Typhoon                                                                  |
|-------------------------------------|-------------------------------------|--------------------------------------------------------------------------|
| `non-empty-string`                  | `non-empty-string`                  | `types::nonEmptyString`                                                  |
| `literal-string`                    | `literal-string`                    | `literal-string`                                                         |
| `'abc'` (string literal)            | `'abc'`                             | `types::string('abc')`                                                   |
| `truthy-string`, `non-falsy-string` | `truthy-string`, `non-falsy-string` | `types::truthyString`, `types::nonFalsyString`                           |
| `numeric-string`                    | `numeric-string`                    | `types::numericString`                                                   |
| `callable-string`                   | `callable-string`                   | `types::callableString`                                                  |
|                                     | `lowercase-string`                  | `types::lowercaseString`                                                 |
|                                     | `non-empty-lowercase-string`        | `types::intersection(types::nonEmptyString, types::lowercaseString)`     |
| `Foo::class`                        | `Foo::class`                        | `types::classConstant(Foo::class, 'class')`, `types::string(Foo::class)` |
| `class-string`                      | `class-string`                      | `types::classString`                                                     |
|                                     | `interface-string`                  |                                                                          |
|                                     | `trait-string`                      |                                                                          |
|                                     | `enum-string`                       |                                                                          |
| `class-string<Foo>`                 | `class-string<Foo>`                 | `types::classString(types::object(Foo::class))`                          |

## Other

| PHPStan           | Psalm             | Typhoon |
|-------------------|-------------------|---------|
| `open-resource`   | `open-resource`   |         |
| `closed-resource` | `closed-resource` |         |
