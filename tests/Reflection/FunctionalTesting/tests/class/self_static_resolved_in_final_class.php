<?php

declare(strict_types=1);

namespace Typhoon\Reflection;

use Typhoon\Type\types;
use function PHPUnit\Framework\assertEquals;

return static function (TyphoonReflector $reflector): void {
    $reflection = $reflector->withResource(new Resource(
        <<<'PHP'
            <?php

            final class A
            {
                public self $self;
                
                /** @var static */
                public $static;
            }
            PHP,
    ))->reflectClass('A');

    assertEquals(types::self('A'), $reflection->properties()['self']->type());
    assertEquals(types::static('A'), $reflection->properties()['static']->type());
};
