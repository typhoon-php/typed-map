<?php

declare(strict_types=1);

namespace Typhoon\TypeComparator;

use PHPUnit\Framework\Attributes\CoversClass;
use Typhoon\Type\types;

#[CoversClass(IsInt::class)]
#[CoversClass(ComparatorSelector::class)]
final class IntTest extends RelationTestCase
{
    protected static function xSubtypeOfY(): iterable
    {
        yield [types::never, types::int(-9, 10)];
        yield [types::int, types::int];
        yield [types::int(-9, 10), types::int(-9, 10)];
        yield [types::int(-9, 10), types::int(-100, 100)];
        yield [types::int(-9, 10), types::int(max: 100)];
        yield [types::int(max: 100), types::int(max: 100)];
        yield [types::int(max: 99), types::int(max: 100)];
        yield [types::int(-9, 10), types::int(min: -100)];
        yield [types::int(min: -100), types::int(min: -100)];
        yield [types::int(min: -9), types::int(min: -100)];
        yield [types::literalValue(1), types::int(0, 1)];
    }

    protected static function xNotSubtypeOfY(): iterable
    {
        yield [types::void, types::int(-1, 1)];
        yield [types::true, types::int(-1, 1)];
        yield [types::false, types::int(-1, 1)];
        yield [types::bool, types::int(-1, 1)];
        yield [types::int, types::int(-1, 1)];
        yield [types::literalInt, types::int(-1, 1)];
        yield [types::positiveInt, types::int(-1, 1)];
        yield [types::negativeInt, types::int(-1, 1)];
        yield [types::intMask(types::literalValue(0)), types::int(-1, 1)];
        yield [types::arrayKey, types::int(-1, 1)];
        yield [types::float, types::int(-1, 1)];
        yield [types::string, types::int(-1, 1)];
        yield [types::nonEmptyString, types::int(-1, 1)];
        yield [types::classString, types::int(-1, 1)];
        yield [types::literalString, types::int(-1, 1)];
        yield [types::truthyString, types::int(-1, 1)];
        yield [types::numericString, types::int(-1, 1)];
        yield [types::array, types::int(-1, 1)];
        yield [types::iterable, types::int(-1, 1)];
        yield [types::object, types::int(-1, 1)];
        yield [types::callable, types::int(-1, 1)];
        yield [types::closure, types::int(-1, 1)];
        yield [types::resource, types::int(-1, 1)];
        yield [types::intersection(types::callable, types::string), types::int(-1, 1)];
        yield [types::mixed, types::int(-1, 1)];
    }
}
