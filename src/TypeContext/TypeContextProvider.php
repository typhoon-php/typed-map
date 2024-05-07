<?php

declare(strict_types=1);

namespace Typhoon\TypeContext;

interface TypeContextProvider
{
    public function typeContext(): TypeContext;
}
