<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;

final class TestBuilder
{
    private ?string $code = null;

    /**
     * @var ?\Closure(TyphoonReflector): void
     */
    private ?\Closure $test = null;

    /**
     * @return array{?string, \Closure(TyphoonReflector): void}
     */
    public function __invoke(): array
    {
        \assert($this->test !== null);

        return [$this->code, $this->test];
    }

    public function code(string $code): self
    {
        if (!str_starts_with($code, '<?php')) {
            $code = '<?php ' . $code;
        }

        $this->code = $code;

        return $this;
    }

    /**
     * @param \Closure(TyphoonReflector): void $test
     */
    public function test(\Closure $test): self
    {
        $this->test = $test;

        return $this;
    }
}
