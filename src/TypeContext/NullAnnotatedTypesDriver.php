<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;

/**
 * @api
 */
final class NullAnnotatedTypesDriver implements AnnotatedTypesDriver
{
    public function reflectTypeDeclarations(ClassLike|FunctionLike $node): TypeDeclarations
    {
        return new TypeDeclarations();
    }
}
