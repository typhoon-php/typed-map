<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\DeclarationId\Id;
use Typhoon\Reflection\TyphoonReflector;
use Typhoon\Type\types;
use Typhoon\Type\Variance;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->reflectCode(
        <<<'PHP'
            <?php
            
            /**
             * @template-covariant T of string 
             */
            class A
            {
            }
            PHP,
    )[Id::namedClass('A')] ?? throw new \LogicException();

    $templates = $reflection->templates();
    assertCount(1, $templates);
    assertArrayHasKey('T', $templates);
    $template = $templates['T'];
    assertSame('T', $template->name);
    assertSame(0, $template->index);
    assertSame(Variance::Covariant, $template->variance());
    assertEquals(types::string, $template->constraint());
};
