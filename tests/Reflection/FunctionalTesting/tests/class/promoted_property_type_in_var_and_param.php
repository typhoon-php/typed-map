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
                /**
                 * @param non-empty-string $onlyParam
                 * @param scalar $paramAndVar
                 */
                public function __construct(
                    public $onlyParam,
                    /**
                     * @var positive-int
                     */
                    public $onlyVar,
                    /**
                     * @var class-string
                     */
                    public $paramAndVar,
                ) {}
            }
            PHP,
    ))->reflectClass('A');

    // TODO
    // $constructor = $reflection->methods()['__construct'];

    assertEquals(types::nonEmptyString, $reflection->properties()['onlyParam']->type());
    // assertEquals(types::nonEmptyString, $constructor->parameters()['onlyParam']->type());
    assertEquals(types::positiveInt, $reflection->properties()['onlyVar']->type());
    // assertEquals(types::nonEmptyString, $constructor->parameters()['onlyVar']->type());
    assertEquals(types::classString, $reflection->properties()['paramAndVar']->type());
    // assertEquals(types::scalar, $constructor->parameters()['paramAndVar']->type());
};
