<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\NodeVisitor;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use Typhoon\TypeContext\TypeContext;

/**
 * @api
 */
final class NullTypeContextProcessor implements TypeContextProcessor
{
    public function process(TypeContext $context, ClassLike|FunctionLike $node): TypeContext
    {
        return $context;
    }
}
