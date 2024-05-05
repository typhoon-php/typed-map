<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\ClassId;
use Typhoon\DeclarationId\FunctionId;
use Typhoon\Reflection\Locator;
use Typhoon\Reflection\Resource;
use Typhoon\TypedMap\TypedMap;

final class TestCodeLocator implements Locator
{
    public function __construct(
        private readonly string $code,
        private readonly TypedMap $data = new TypedMap(),
    ) {}

    public function locate(ClassId|FunctionId $id): ?Resource
    {
        return new Resource($this->code, $this->data);
    }
}
