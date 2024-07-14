<?php

declare(strict_types=1);

namespace Typhoon\DeclarationId;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
final class IdClassFactoryTest extends TestCase
{
    #[TestWith([__DIR__ . '/Fixtures/anonymous_class_3_12.php', 3])]
    #[TestWith([__DIR__ . '/Fixtures/anonymous_array_object_5_5.php', 5])]
    public function testItCreatesAnonymousClassIdFromAnonymousClassName(string $file, int $line): void
    {
        /**
         * @psalm-suppress UnresolvableInclude
         * @var object
         */
        $object = require_once $file;
        $name = $object::class;

        $classId = Id::class($name);

        self::assertInstanceOf(AnonymousClassId::class, $classId);
        self::assertSame($file, $classId->file);
        self::assertSame($line, $classId->line);
        self::assertNull($classId->column);
        self::assertSame($name, $classId->name);
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

    public function staticAnalysisTestItInfersExistingClassName(): void
    {
        $id = Id::class(\stdClass::class);
        /** @psalm-check-type-exact $id = NamedClassId<\stdClass::class>|AnonymousClassId<\stdClass::class> */
        $_name = $id->name;
        /** @psalm-check-type-exact $_name = \stdClass::class */
    }

    public function staticAnalysisTestItInfersObjectClassName(): void
    {
        $object = new \ArrayObject(['a']);
        $id = Id::class($object::class);
        /** @psalm-check-type-exact $id = NamedClassId<class-string<\ArrayObject<0, 'a'>>>|AnonymousClassId<class-string<\ArrayObject<0, 'a'>>> */
        $_name = $id->name;
        /** @psalm-check-type-exact $_name = class-string<\ArrayObject<0, 'a'>> */
    }

    public function staticAnalysisTestItInfersStringName(): void
    {
        $id = Id::class('someClass');
        /** @psalm-check-type-exact $id = NamedClassId<'someClass'>|AnonymousClassId<null> */
        $_name = $id->name;
        /** @psalm-check-type-exact $_name = ?'someClass' */
    }
}
