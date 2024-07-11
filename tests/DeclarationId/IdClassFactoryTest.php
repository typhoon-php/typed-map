<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
final class IdClassFactoryTest extends TestCase
{
    #[TestWith([new \stdClass()])]
    #[TestWith([new \SplFileInfo(__FILE__)])]
    public function testItCreatesNamedClassIdFromObjectOfNamedClass(object $object): void
    {
        $classId = Id::class($object);

        self::assertInstanceOf(NamedClassId::class, $classId);
        self::assertSame($object::class, $classId->name);
    }

    #[TestWith([__DIR__ . '/Fixtures/anonymous_class_3_12.php', 3, 12])]
    #[TestWith([__DIR__ . '/Fixtures/anonymous_array_object_5_5.php', 5, 5])]
    public function testItCreatesAnonymousClassIdFromObjectOfNamedClass(string $file, int $line, int $column): void
    {
        /**
         * @psalm-suppress UnresolvableInclude
         * @var object
         */
        $object = require_once $file;

        $classId = Id::class($object);

        self::assertInstanceOf(AnonymousClassId::class, $classId);
        self::assertSame($file, $classId->file);
        self::assertSame($line, $classId->line);
        self::assertSame($column, $classId->column);
    }

    /**
     * @param non-empty-string $class
     */
    #[TestWith([\stdClass::class])]
    #[TestWith([\SplFileInfo::class])]
    public function testItCreatesNamedClassIdFromNamedClassString(string $class): void
    {
        $classId = Id::class($class);

        self::assertInstanceOf(NamedClassId::class, $classId);
        self::assertSame($class, $classId->name);
    }
}
