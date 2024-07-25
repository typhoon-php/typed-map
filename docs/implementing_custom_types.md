# Implementing custom types

```php
use Typhoon\Reflection\Annotated\CustomTypeResolver;
use Typhoon\Reflection\Annotated\TypeContext;
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\Type;
use Typhoon\Type\types;
use Typhoon\Type\TypeVisitor;
use function Typhoon\Type\stringify;

/**
 * @implements Type<int|float>
 */
enum binaryTypes: string implements Type, CustomTypeResolver
{
    case int16 = 'int16';
    case int32 = 'int32';
    const int64 = types::int;
    case float32 = 'float32';
    const float64 = types::float;

    public function accept(TypeVisitor $visitor): mixed
    {
        /**
         * We need to suppress here, because Psalm does not support var annotations on enum cases yet ;(
         * @psalm-suppress InvalidArgument
         */
        return match ($this) {
            self::int16 => $visitor->int($this, -32768, 32767),
            self::int32 => $visitor->int($this, -2147483648, 2147483647),
            self::float32 => $visitor->float($this, -3.40282347E+38, 3.40282347E+38),
        };
    }

    public function resolveCustomType(string $name, array $typeArguments, TypeContext $context): ?Type
    {
        return self::tryFrom($name);
    }
}

final readonly class Message
{
    /**
     * @param list<int16> $some16bitIntegers
     */
    public function __construct(
        public array $some16bitIntegers,
    ) {}
}

$reflector = TyphoonReflector::build(customTypeResolver: binaryTypes::int16);

$propertyType = $reflector
    ->reflectClass(Message::class)
    ->properties()['some16bitIntegers']
    ->type();

echo stringify($propertyType), PHP_EOL; // list<int<-32768, 32767>>
```
