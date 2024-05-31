<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use PHPUnit\Framework\Attributes\CoversClass;
use Typhoon\Type\types;

#[CoversClass(IsLiteral::class)]
#[CoversClass(ComparatorSelector::class)]
final class LiteralTest extends RelationTestCase
{
    protected static function xSubtypeOfY(): iterable
    {
        yield [types::never, types::literalInt];
        yield [types::literalInt, types::literalInt];
        yield [types::literalString, types::literalString];
        yield [types::true, types::literal(types::bool)];
        yield [types::false, types::literal(types::bool)];
        yield [types::intValue(1), types::literalInt];
        yield [types::floatValue(M_PI), types::literal(types::float)];
        yield [types::stringValue('abc'), types::literalString];
        yield [types::classConstant(\stdClass::class, 'class'), types::literalString];
    }

    protected static function xNotSubtypeOfY(): iterable
    {
        yield [types::void, types::literalString];
        yield [types::true, types::literalString];
        yield [types::false, types::literalString];
        yield [types::bool, types::literalString];
        yield [types::int, types::literalString];
        yield [types::literalInt, types::literalString];
        yield [types::positiveInt, types::literalString];
        yield [types::negativeInt, types::literalString];
        yield [types::intMask(types::intValue(0)), types::literalString];
        yield [types::arrayKey, types::literalString];
        yield [types::float, types::literalString];
        yield [types::string, types::literalString];
        yield [types::nonEmptyString, types::literalString];
        yield [types::classString, types::literalString];
        yield [types::truthyString, types::literalString];
        yield [types::numericString, types::literalString];
        yield [types::array, types::literalString];
        yield [types::iterable, types::literalString];
        yield [types::object, types::literalString];
        yield [types::callable, types::literalString];
        yield [types::closure, types::literalString];
        yield [types::resource, types::literalString];
        yield [types::intersection(types::callable, types::string), types::literalString];
        yield [types::mixed, types::literalString];
    }
}
