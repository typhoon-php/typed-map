<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\NodeVisitor;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use Typhoon\TypeContext\TypeContext;

/**
 * @api
 */
interface TypeContextProcessor
{
    public function process(TypeContext $context, ClassLike|FunctionLike $node): TypeContext;
}
