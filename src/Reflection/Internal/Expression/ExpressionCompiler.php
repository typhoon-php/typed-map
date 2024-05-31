<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\Expression;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\VariadicPlaceholder;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 */
final class ExpressionCompiler
{
    /**
     * @return ($expr is null ? null : Expression)
     */
    public function compile(?Expr $expr): ?Expression
    {
        return match (true) {
            $expr === null => null,
            $expr instanceof Scalar\String_,
            $expr instanceof Scalar\LNumber,
            $expr instanceof Scalar\DNumber => new Value($expr->value),
            $expr instanceof Expr\Array_ => $this->compileArray($expr),
            $expr instanceof Scalar\MagicConst\Line => new Value($expr->getStartLine()),
            $expr instanceof Scalar\MagicConst\File => MagicFile::Constant,
            $expr instanceof Scalar\MagicConst\Dir => MagicDir::Constant,
            $expr instanceof Scalar\MagicConst\Namespace_ => MagicNamespace::Constant,
            $expr instanceof Scalar\MagicConst\Function_ => MagicFunction::Constant,
            $expr instanceof Scalar\MagicConst\Class_ => MagicClass::Constant,
            $expr instanceof Scalar\MagicConst\Trait_ => MagicTrait::Constant,
            $expr instanceof Scalar\MagicConst\Method => MagicMethod::Constant,
            $expr instanceof Coalesce && $expr->left instanceof Expr\ArrayDimFetch => new ArrayFetchCoalesce(
                array: $this->compile($expr->left->var),
                key: $this->compile($expr->left->dim ?? throw new \LogicException()),
                default: $this->compile($expr->right),
            ),
            $expr instanceof Expr\BinaryOp => new BinaryOperation(
                left: $this->compile($expr->left),
                right: $this->compile($expr->right),
                operator: $expr->getOperatorSigil(),
            ),
            $expr instanceof Expr\UnaryPlus => new UnaryOperation($this->compile($expr->expr), '+'),
            $expr instanceof Expr\UnaryMinus => new UnaryOperation($this->compile($expr->expr), '-'),
            $expr instanceof Expr\BooleanNot => new UnaryOperation($this->compile($expr->expr), '!'),
            $expr instanceof Expr\BitwiseNot => new UnaryOperation($this->compile($expr->expr), '~'),
            $expr instanceof Expr\ConstFetch => $this->compileConstant($expr->name),
            $expr instanceof Expr\ArrayDimFetch && $expr->dim !== null => new ArrayFetch(
                array: $this->compile($expr->var),
                key: $this->compileIdentifier($expr->dim),
            ),
            $expr instanceof Expr\ClassConstFetch => new ClassConstantFetch(
                class: $this->compileClassName($expr->class),
                name: $this->compileIdentifier($expr->name),
            ),
            $expr instanceof Expr\New_ => new Instantiation(
                class: $this->compileClassName($expr->class),
                arguments: $this->compileArguments($expr->args),
            ),
            $expr instanceof Expr\Ternary => new Ternary(
                condition: $this->compile($expr->cond),
                if: $this->compile($expr->if),
                else: $this->compile($expr->else),
            ),
            default => throw new \LogicException($expr::class),
        };
    }

    private function compileConstant(Name $name): Expression
    {
        $lowerStringName = $name->toLowerString();

        if ($lowerStringName === 'null') {
            return new Value(null);
        }

        if ($lowerStringName === 'true') {
            return new Value(true);
        }

        if ($lowerStringName === 'false') {
            return new Value(false);
        }

        $namespacedName = $name->getAttribute('namespacedName');

        if ($namespacedName instanceof FullyQualified) {
            return new ConstantFetch(
                name: $namespacedName->toString(),
                globalName: $name->toString(),
            );
        }

        return new ConstantFetch($name->toString());
    }

    private function compileArray(Expr\Array_ $expr): Expression
    {
        $items = array_values(array_filter($expr->items));

        if ($items === []) {
            return new Value([]);
        }

        return new ArrayExpression(array_map(
            fn(Expr\ArrayItem $item): ArrayElement => new ArrayElement(
                key: $item->unpack ? true : $this->compile($item->key),
                value: $this->compile($item->value),
            ),
            $items,
        ));
    }

    private function compileClassName(Name|Expr|Class_ $name): Expression
    {
        if ($name instanceof Expr) {
            return $this->compile($name);
        }

        if ($name instanceof Name) {
            if ($name->isSpecialClassName()) {
                return match ($name->toLowerString()) {
                    'self' => MagicClass::Constant,
                    'parent' => ParentClass::Instance,
                    'static' => throw new \LogicException('static'),
                };
            }

            return new Value($name->toString());
        }

        throw new \LogicException('anonymous');
    }

    private function compileIdentifier(Expr|Identifier $name): Expression
    {
        if ($name instanceof Identifier) {
            return new Value($name->name);
        }

        return $this->compile($name);
    }

    /**
     * @param array<Arg|VariadicPlaceholder> $arguments
     * @return array<Expression>
     */
    private function compileArguments(array $arguments): array
    {
        $compiled = [];

        foreach ($arguments as $argument) {
            if ($argument instanceof VariadicPlaceholder) {
                throw new \LogicException();
            }

            if ($argument->name === null) {
                $compiled[] = $this->compile($argument->value);

                continue;
            }

            $compiled[$argument->name->name] = $this->compile($argument->value);
        }

        return $compiled;
    }
}
