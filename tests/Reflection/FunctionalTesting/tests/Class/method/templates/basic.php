<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\types;
use Typhoon\Type\Variance;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;
use function Typhoon\DeclarationId\classId;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            class A
            {
                /**
                 * @template T of string 
                 */
                public function a() {}
            }
            PHP,
    )[classId('A')]->method('a') ?? throw new \LogicException();

    $templates = $reflection->templates();
    assertCount(1, $templates);
    assertArrayHasKey('T', $templates);
    /** @psalm-suppress PossiblyUndefinedStringArrayOffset */
    $template = $templates['T'];
    assertSame('T', $template->name);
    assertSame(0, $template->index);
    assertSame(Variance::Invariant, $template->variance());
    assertEquals(types::string, $template->constraint());
};