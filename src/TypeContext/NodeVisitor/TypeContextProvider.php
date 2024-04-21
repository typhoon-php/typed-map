<?php

declare(strict_types=1);

namespace Typhoon\TypeContext\NodeVisitor;

use Typhoon\TypeContext\TypeContext;

interface TypeContextProvider
{
    public function typeContext(): TypeContext;
}
