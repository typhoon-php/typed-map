<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\Expression;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression as ExpressionNode;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\Reflector;

#[CoversClass(ExpressionCompiler::class)]
final class ExpressionCompilerTest extends TestCase
{
    private static ?Parser $parser = null;

    /**
     * @return \Generator<string, array{string}>
     */
    public static function expressionProvider(): \Generator
    {
        foreach (self::expressions() as $expression) {
            yield $expression => [$expression];
        }
    }

    /**
     * @return \Generator<non-empty-string>
     */
    private static function expressions(): \Generator
    {
        yield 'null';
        yield 'true';
        yield 'false';
        yield '!false';
        yield '!true';
        yield '1';
        yield '1 + 2 + 3';
        yield '1 + -2 + 3';
        yield '-1 - 3 - 123.5';
        yield '+10';
        yield '0.543';
        yield "'a'";
        yield '"a"';
        yield '"a"."b"';
        yield '1 / 2';
        yield '1 / 2 / 3';
        yield 'new stdClass()';
        yield 'new ("std"."Class")()';
        yield 'PHP_VERSION_ID';
        // yield 'ArrayObject::ARRAY_AS_PROPS';
        yield 'true ? 1 : 2';
        yield 'true ?: 2';
        yield '(1 + 2) ?: 2';
        yield '~0b01';
        yield '[]';
        yield '[1 => 1]';
        yield "[1 => 1 + 1, 'a' => 'b' . 'c']";
        yield '[[1, 2, 3]]';
        yield '[...[1, 2, 3]]';
        yield '__LINE__';
        // yield '__CLASS__';
        yield '__TRAIT__';
        yield '__FUNCTION__';
        // yield '__METHOD__';
        yield 'null ?? 1';
        yield '[1][0]';
        yield '[1][1] ?? 2';
    }

    #[DataProvider('expressionProvider')]
    public function test(string $expressionCode): void
    {
        $expressionNode = $this->parseExpression($expressionCode);
        $expected = $this->evalExpression($expressionCode);
        $compiled = (new ExpressionCompiler())->compile($expressionNode);

        $value = $compiled->evaluate($this->createMock(Reflector::class));

        self::assertEquals($expected, $value);
    }

    public function testConstantFetchWithClassExpression(): void
    {
        self::markTestSkipped();

        $expressionNode = $this->parseExpression("('Array'.'Object')::{'ARRAY'.'_AS_PROPS'}");
        $compiled = (new ExpressionCompiler())->compile($expressionNode);

        $value = $compiled->evaluate($this->createMock(Reflector::class));

        self::assertEquals(\ArrayObject::ARRAY_AS_PROPS, $value);
    }

    private function parseExpression(string $expressionCode): Expr
    {
        self::$parser ??= (new ParserFactory())->createForHostVersion();
        $exprNode = (self::$parser->parse('<?php ' . $expressionCode . ';') ?? [])[0] ?? null;
        \assert($exprNode instanceof ExpressionNode);

        return $exprNode->expr;
    }

    private function evalExpression(string $expressionCode): mixed
    {
        /** @psalm-suppress ForbiddenCode */
        return eval(sprintf('return %s;', $expressionCode));
    }
}
