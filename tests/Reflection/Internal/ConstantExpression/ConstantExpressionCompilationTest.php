<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\ConstantExpression;

use PhpParser\Node;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Typhoon\Reflection\Internal\Context\ContextProvider;
use Typhoon\Reflection\Internal\Context\ContextVisitor;
use Typhoon\Reflection\Internal\PhpParser\ConstantExpressionCompiler;

#[CoversClass(ArrayElement::class)]
#[CoversClass(ArrayExpression::class)]
#[CoversClass(ArrayFetch::class)]
#[CoversClass(ArrayFetchCoalesce::class)]
#[CoversClass(BinaryOperation::class)]
#[CoversClass(ClassConstantFetch::class)]
#[CoversClass(ConstantFetch::class)]
#[CoversClass(Instantiation::class)]
#[CoversClass(Ternary::class)]
#[CoversClass(UnaryOperation::class)]
#[CoversClass(Value::class)]
#[CoversClass(ConstantExpressionCompiler::class)]
final class ConstantExpressionCompilationTest extends TestCase
{
    private static ?Parser $parser = null;

    #[TestWith(['return null;'])]
    #[TestWith(['return true;'])]
    #[TestWith(['return false;'])]
    #[TestWith(['return !false;'])]
    #[TestWith(['return !true;'])]
    #[TestWith(['return -1;'])]
    #[TestWith(['return 0;'])]
    #[TestWith(['return 1;'])]
    #[TestWith(['return 1 + 2 + 3;'])]
    #[TestWith(['return 1 + -2 + 3;'])]
    #[TestWith(['return -1 - 3 - 123.5;'])]
    #[TestWith(['return +10;'])]
    #[TestWith(['return 0.543;'])]
    #[TestWith(["return '';"])]
    #[TestWith(["return 'a';"])]
    #[TestWith(['return "a";'])]
    #[TestWith(['return "a"."b";'])]
    #[TestWith(['return 1 / 2;'])]
    #[TestWith(['return 1 / 2 / 3;'])]
    #[TestWith(['return new stdClass();'])]
    #[TestWith(['return new ArrayObject([1,2,3]);'])]
    #[TestWith(["return new Exception(code: 123, message: 'message');"])]
    #[TestWith(['namespace CompilerTest; return new \stdClass();'])]
    #[TestWith(['return new ("std"."Class")();'])]
    #[TestWith(['namespace CompilerTest; return new ("std"."Class")();'])]
    #[TestWith(['return PHP_VERSION_ID;'])]
    #[TestWith(['return ArrayObject::ARRAY_AS_PROPS;'])]
    #[TestWith(['return true ? 1 : 2;'])]
    #[TestWith(['return true ?: 2;'])]
    #[TestWith(['return (1 + 2) ?: 2;'])]
    #[TestWith(['return ~0b01;'])]
    #[TestWith(['return 0b11 & 0b1;'])]
    #[TestWith(['return 0b11 << 0b1;'])]
    #[TestWith(['return 0b1100000 >> 0b1;'])]
    #[TestWith(['return 0b01 | 0b10;'])]
    #[TestWith(['return 0b01 ^ 0b10;'])]
    #[TestWith(['return true && false;'])]
    #[TestWith(['return true and false;'])]
    #[TestWith(['return true || false;'])]
    #[TestWith(['return true or false;'])]
    #[TestWith(['return true xor false;'])]
    #[TestWith(["return 1 == '1';"])]
    #[TestWith(["return 1 != '1';"])]
    #[TestWith(['return 10 < 2;'])]
    #[TestWith(['return 10 > 2;'])]
    #[TestWith(['return 10 >= 2;'])]
    #[TestWith(['return 10 <= 2;'])]
    #[TestWith(["return 10 === '2';"])]
    #[TestWith(["return 10 <=> '2';"])]
    #[TestWith(["return 10 !== '2';"])]
    #[TestWith(['return 10 % 2;'])]
    #[TestWith(['return 10 * 2;'])]
    #[TestWith(['return 10 ** 2;'])]
    #[TestWith(['return [];'])]
    #[TestWith(['return [1 => 1];'])]
    #[TestWith(["return [1 => 1 + 1, 'a' => 'b' . 'c'];"])]
    #[TestWith(['return [[1, 2, 3]];'])]
    #[TestWith(['return [...[1, 2, 3]];'])]
    #[TestWith(['return __LINE__;'])]
    #[TestWith(['return __CLASS__;'])]
    #[TestWith(['return __TRAIT__;'])]
    #[TestWith(['return __FUNCTION__;'])]
    #[TestWith(['return __METHOD__;'])]
    #[TestWith(['return null ?? 1;'])]
    #[TestWith(['return [1][0];'])]
    #[TestWith(['return [1][1] ?? 2;'])]
    #[TestWith(['return stdClass::class;'])]
    #[TestWith(['namespace CompilerTest; const Y = 123; return Y;'])]
    #[TestWith(['namespace CompilerTest; return \PHP_INT_MAX;'])]
    #[TestWith([
        <<<'PHP'
            namespace CompilerTest;
            class A extends \ArrayObject {
                public static function get() { return [self::class, parent::class, __CLASS__, __TRAIT__, __METHOD__]; }
            }
            return A::get();
            PHP,
    ])]
    // #[TestWith([
    //     <<<'PHP'
    //         namespace CompilerTest;
    //         trait T {
    //             public static function get() { return [self::class, __CLASS__, __TRAIT__, __METHOD__]; }
    //         }
    //         return T::get();
    //         PHP,
    // ])]
    #[TestWith([
        <<<'PHP'
            namespace CompilerTest;
            class G {
                public static function get() {
                    new class {};
                
                    return self::class;
                }
            }
            return G::get();
            PHP,
    ])]
    #[TestWith([
        <<<'PHP'
            namespace CompilerTest;
            function a() { return __FUNCTION__; }
            return a();
            PHP,
    ])]
    // #[TestWith([
    //     <<<'PHP'
    //         namespace CompilerTest;
    //         return (function () { return __FUNCTION__; })();
    //         PHP,
    // ])]
    // #[TestWith([
    //     <<<'PHP'
    //         namespace CompilerTest;
    //         function b() { return (function () { return __FUNCTION__; })(); }
    //         return b();
    //         PHP,
    // ])]
    public function test(string $code): void
    {
        $compiled = $this->compile($code);
        $expected = $this->eval($code);

        $evaluated = $compiled->evaluate();

        self::assertEquals($expected, $evaluated);
    }

    public function testConstantFetchWithClassExpression(): void
    {
        $compiled = $this->compile("return ('Array'.'Object')::{'ARRAY'.'_AS_PROPS'};");

        $evaluated = $compiled->evaluate();

        self::assertEquals(\ArrayObject::ARRAY_AS_PROPS, $evaluated);
    }

    private function compile(string $code): Expression
    {
        self::$parser ??= (new ParserFactory())->createForHostVersion();

        $code = '<?php ' . $code;
        $nameResolver = new NameResolver();
        $contextVisitor = new ContextVisitor('file.php', $code, $nameResolver->getNameContext());
        $traverser = new NodeTraverser();
        $traverser->addVisitor($nameResolver);
        $traverser->addVisitor($contextVisitor);
        $traverser->addVisitor($visitor = new class ($contextVisitor) extends NodeVisitorAbstract {
            public ?Expression $expression = null;

            public function __construct(private readonly ContextProvider $contextProvider) {}

            public function leaveNode(Node $node): ?int
            {
                if ($node instanceof Return_) {
                    $this->expression ??= (new ConstantExpressionCompiler($this->contextProvider->current()))->compile($node->expr);
                }

                return null;
            }
        });
        $traverser->traverse(self::$parser->parse($code) ?? []);

        \assert($visitor->expression !== null);

        return $visitor->expression;
    }

    private function eval(string $code): mixed
    {
        /** @psalm-suppress ForbiddenCode */
        return eval($code);
    }
}
