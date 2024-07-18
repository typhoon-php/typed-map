<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpDoc::class)]
#[CoversClass(PhpDocParser::class)]
final class PhpDocAndParserTest extends TestCase
{
    public function testHasDeprecatedReturnsFalseIfNoDeprecatedTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $deprecated = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->hasDeprecated();

        self::assertFalse($deprecated);
    }

    public function testHasDeprecatedReturnsTrueIfDeprecated(): void
    {
        $parser = new PhpDocParser(lines: false);

        $deprecated = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @deprecated
                 */
                PHP,
        )->hasDeprecated();

        self::assertTrue($deprecated);
    }

    public function testHasFinalReturnsFalseIfNoFinalTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $final = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->hasFinal();

        self::assertFalse($final);
    }

    public function testHasFinalReturnsTrueIfFinal(): void
    {
        $parser = new PhpDocParser(lines: false);

        $final = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @final
                 */
                PHP,
        )->hasFinal();

        self::assertTrue($final);
    }

    public function testHasReadonlyReturnsFalseIfNoReadonlyTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $readonly = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->hasReadonly();

        self::assertFalse($readonly);
    }

    public function testHasReadonlyReturnsTrueIfReadonly(): void
    {
        $parser = new PhpDocParser(lines: false);

        $readonly = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @readonly
                 */
                PHP,
        )->hasReadonly();

        self::assertTrue($readonly);
    }

    public function testItReturnsNullVarTypeWhenNoVarTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $varType = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->varType();

        self::assertNull($varType);
    }

    public function testItReturnsLatestPrioritizedVarTagType(): void
    {
        $parser = new PhpDocParser(lines: false);

        $varType = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @var int
                 * @psalm-var float
                 * @psalm-var string
                 */
                PHP,
        )->varType();

        self::assertEquals(new IdentifierTypeNode('string'), $varType);
    }

    public function testItReturnsNullParamTypeWhenNoParamTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $paramTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->paramTypes();

        self::assertEmpty($paramTypes);
    }

    public function testItReturnsLatestPrioritizedParamTagType(): void
    {
        $parser = new PhpDocParser(lines: false);

        $paramTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @param int $a
                 * @param object $b
                 * @param mixed $b
                 * @psalm-param float $a
                 * @psalm-param string $a
                 */
                PHP,
        )->paramTypes();

        self::assertEquals(
            [
                'a' => new IdentifierTypeNode('string'),
                'b' => new IdentifierTypeNode('mixed'),
            ],
            $paramTypes,
        );
    }

    public function testItReturnsNullReturnTypeWhenNoReturnTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $returnType = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->returnType();

        self::assertNull($returnType);
    }

    public function testItReturnsLatestPrioritizedReturnTagType(): void
    {
        $parser = new PhpDocParser(lines: false);

        $returnType = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @return int
                 * @psalm-return float
                 * @psalm-return string
                 */
                PHP,
        )->returnType();

        self::assertEquals(new IdentifierTypeNode('string'), $returnType);
    }

    public function testItReturnsAllThrowsTypes(): void
    {
        $parser = new PhpDocParser(lines: false);

        $throwsTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @throws RuntimeException|LogicException
                 * @throws \Exception
                 * @phpstan-throws \OutOfBoundsException
                 */
                PHP,
        )->throwsTypes();

        self::assertEquals(
            [
                new UnionTypeNode([
                    new IdentifierTypeNode('RuntimeException'),
                    new IdentifierTypeNode('LogicException'),
                ]),
                new IdentifierTypeNode('\Exception'),
                new IdentifierTypeNode('\OutOfBoundsException'),
            ],
            $throwsTypes,
        );
    }

    public function testItReturnsEmptyTemplatesWhenNoTemplateTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $templateTags = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->templateTags();

        self::assertEmpty($templateTags);
    }

    public function testItReturnsLatestPrioritizedTemplates(): void
    {
        $parser = new PhpDocParser(lines: false);

        $templates = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 * @template T of int
                 * @template T2 of object
                 * @template T2 of mixed
                 * @psalm-template T of float
                 * @psalm-template T of string
                 */
                PHP,
        )->templateTags();

        self::assertEquals(
            [
                new TemplateTagValueNode('T', new IdentifierTypeNode('string'), ''),
                new TemplateTagValueNode('T2', new IdentifierTypeNode('mixed'), ''),
            ],
            array_column($templates, 'value'),
        );
    }

    public function testItReturnsEmptyExtendedTypesWhenNoExtendsTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $extendedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->extendedTypes();

        self::assertEmpty($extendedTypes);
    }

    public function testItReturnsLatestPrioritizedExtendedTypes(): void
    {
        $parser = new PhpDocParser(lines: false);

        $extendedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 *
                 * @extends C<int>
                 * @extends D<object>
                 * @extends D<mixed>
                 * @phpstan-extends C<float>
                 * @phpstan-extends C<string>
                 */
                PHP,
        )->extendedTypes();

        self::assertEquals(
            [
                $this->createGenericTypeNode(new IdentifierTypeNode('C'), [new IdentifierTypeNode('string')]),
                $this->createGenericTypeNode(new IdentifierTypeNode('D'), [new IdentifierTypeNode('mixed')]),
            ],
            $extendedTypes,
        );
    }

    public function testItReturnsEmptyImplementedTypesWhenNoImplementsTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $implementedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->implementedTypes();

        self::assertEmpty($implementedTypes);
    }

    public function testItReturnsLatestPrioritizedImplementedTypes(): void
    {
        $parser = new PhpDocParser(lines: false);

        $implementedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 *
                 * @implements C<int>
                 * @implements D<object>
                 * @implements D<mixed>
                 * @phpstan-implements C<float>
                 * @phpstan-implements C<string>
                 */
                PHP,
        )->implementedTypes();

        self::assertEquals(
            [
                $this->createGenericTypeNode(new IdentifierTypeNode('C'), [new IdentifierTypeNode('string')]),
                $this->createGenericTypeNode(new IdentifierTypeNode('D'), [new IdentifierTypeNode('mixed')]),
            ],
            $implementedTypes,
        );
    }

    public function testItReturnsEmptyUsedTypesWhenNoImplementsTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $usedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->usedTypes();

        self::assertEmpty($usedTypes);
    }

    public function testItReturnsLatestPrioritizedUsedTypes(): void
    {
        $parser = new PhpDocParser(lines: false);

        $usedTypes = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 *
                 * @use C<int>
                 * @use D<object>
                 * @use D<mixed>
                 * @phpstan-use C<float>
                 * @phpstan-use C<string>
                 */
                PHP,
        )->usedTypes();

        self::assertEquals(
            [
                $this->createGenericTypeNode(new IdentifierTypeNode('C'), [new IdentifierTypeNode('string')]),
                $this->createGenericTypeNode(new IdentifierTypeNode('D'), [new IdentifierTypeNode('mixed')]),
            ],
            $usedTypes,
        );
    }

    public function testItReturnsEmptyTypeAliasesWhenNoTypeTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $typeAliases = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->typeAliases();

        self::assertEmpty($typeAliases);
    }

    public function testItReturnsLatestPrioritizedTypeAliases(): void
    {
        $parser = new PhpDocParser(lines: false);

        $typeAliases = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 *
                 * @phpstan-type A = string
                 * @phpstan-type B = object
                 * @phpstan-type B = mixed
                 * @psalm-type A int
                 * @psalm-type A float
                 */
                PHP,
        )->typeAliases();

        self::assertEquals(
            [
                new TypeAliasTagValueNode('A', new IdentifierTypeNode('float')),
                new TypeAliasTagValueNode('B', new IdentifierTypeNode('mixed')),
            ],
            $typeAliases,
        );
    }

    public function testItReturnsEmptyTypeAliasImportsWhenNoTypeTag(): void
    {
        $parser = new PhpDocParser(lines: false);

        $typeAliasImports = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 */
                PHP,
        )->typeAliasImports();

        self::assertEmpty($typeAliasImports);
    }

    public function testItReturnsLatestPrioritizedTypeAliasImports(): void
    {
        $parser = new PhpDocParser(lines: false);

        $typeAliasImports = $parser->parse(
            <<<'PHP'
                /**
                 * @example
                 *
                 * @phpstan-import-type A from string
                 * @phpstan-import-type B from object
                 * @phpstan-import-type B from mixed
                 * @psalm-import-type A from int
                 * @psalm-import-type A from float
                 * @psalm-import-type C from bool as A
                 */
                PHP,
        )->typeAliasImports();

        self::assertEquals(
            [
                new TypeAliasImportTagValueNode('C', new IdentifierTypeNode('bool'), 'A'),
                new TypeAliasImportTagValueNode('B', new IdentifierTypeNode('mixed'), null),
            ],
            $typeAliasImports,
        );
    }

    public function testItCachesPriority(): void
    {
        $tagPrioritizer = $this->createMock(PhpDocTagPrioritizer::class);
        $tagPrioritizer->expects(self::exactly(3))->method('priorityFor')->willReturn(0);
        $parser = new PhpDocParser(tagPrioritizer: $tagPrioritizer);

        $parser->parse(
            <<<'PHP'
                /**
                 * @param string $a
                 * @param string $a
                 * @param string $a
                 */
                PHP,
        )->paramTypes();
    }

    public function testMethodsMemoized(): void
    {
        $phpDoc = (new PhpDocParser())->parse(
            <<<'PHP'
                /**
                 * @template T
                 * @implements Iterator
                 * @use Iterator
                 * @extends stdClass
                 * @var string
                 * @param int $x
                 * @param float $y
                 * @return array
                 * @phpstan-type A int
                 * @phpstan-import-type C from bool as A
                 * @phpstan-throws RuntimeException
                 * @throws LogicException
                 */
                PHP,
        );
        $tags = static fn(): array => [
            $phpDoc->templateTags(),
            $phpDoc->implementedTypes(),
            $phpDoc->usedTypes(),
            $phpDoc->extendedTypes(),
            $phpDoc->varType(),
            $phpDoc->paramTypes(),
            $phpDoc->returnType(),
            $phpDoc->typeAliases(),
            $phpDoc->typeAliasImports(),
            $phpDoc->throwsTypes(),
        ];

        $first = $tags();
        $second = $tags();

        self::assertSame($first, $second);
    }

    /**
     * @param list<TypeNode> $genericTypes
     */
    private function createGenericTypeNode(IdentifierTypeNode $type, array $genericTypes): GenericTypeNode
    {
        return new GenericTypeNode(
            type: $type,
            genericTypes: $genericTypes,
            variances: array_fill(0, \count($genericTypes), GenericTypeNode::VARIANCE_INVARIANT),
        );
    }
}
