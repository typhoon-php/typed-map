# Typhoon Types Interoperability

## `null`, `void`, `never`

| PHPStan | Psalm   | Typhoon        |
|---------|---------|----------------|
| `null`  | `null`  | `types::null`  |
| `void`  | `void`  | `types::void`  |
| `never` | `never` | `types::never` |

## Boolean

| PHPStan | Psalm   | Typhoon        |
|---------|---------|----------------|
| `true`  | `true`  | `types::true`  |
| `false` | `false` | `types::false` |
| `bool`  | `bool`  | `types::bool`  |

## Integer

| PHPStan                   | Psalm                        | Typhoon                                                      |
|---------------------------|------------------------------|--------------------------------------------------------------|
| `int`                     | `int`                        | `types::int`                                                 |
| `positive-int`            | `positive-int`               | `types::positiveInt`                                         |
| `negative-int`            | `negative-int`               | `types::negativeInt`                                         |
| `non-positive-int`        | `non-positive-int`           | `types::nonPositiveInt`                                      |
| `non-negative-int`        | `non-negative-int`           | `types::nonNegativeInt`                                      |
| `non-zero-int`            | `negative-int\|positive-int` | `types::nonZeroInt`                                          |
| `int<-5, 6>`              | `int<-5, 6>`                 | `types::intRange(-5, 6)`                                     |
| `int<min, 6>`             | `int<min, 6>`                | `types::intRange(max: 6)`                                    |
| `int<-5, max>`            | `int<-5, max>`               | `types::intRange(min: -5)`                                   |
| `int-mask<1,2,4>`         | `int-mask<1, 2, 4>`          | `types::intMask(1,2,4)`                                      |
| `int-mask-of<Foo::INT_*>` | `int-mask-of<Foo::INT_*>`    | `types::intMaskOf(types::classConstant(Foo::class, 'INT_*')` |
| `literal-int`             | `literal-int`                | `types::literalInt`                                          |
| `123` (literal)           | `123`                        | `types::int(123)`                                            |
