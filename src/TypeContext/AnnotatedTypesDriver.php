<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;

/**
 * @api
 */
interface AnnotatedTypesDriver
{
    public function reflectTypeDeclarations(ClassLike|FunctionLike $node): TypeDeclarations;
}
