<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\Resource;

final class TestCodeLocator implements Locator
{
    public function __construct(
        private readonly string $code,
    ) {}

    public function locate(ClassId|FunctionId $id): ?Resource
    {
        return new Resource($this->code);
    }
}
