<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use PHPUnit\Framework\TestCase;
use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\Exception\FileIsNotReadable;
use Typhoon\Reflection\Exception\LocatorFailed;
use Typhoon\Reflection\Locator\FileAnonymousLocator;

return static function (TyphoonReflector $reflector, TestCase $test): void {
    $file = 'some-wrong-file.php';
    $id = Id::anonymousClass($file, 10, 20);

    $test->expectExceptionObject(new LocatorFailed(FileAnonymousLocator::class, $id, new FileIsNotReadable($file)));

    $reflector->reflect($id);
};
