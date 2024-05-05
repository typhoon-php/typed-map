<?php

declare(strict_types=1);

namespace Typhoon\Reflection\FunctionalTesting;

use Typhoon\Reflection\TyphoonReflector;
use Typhoon\TypedMap\Key;
use Typhoon\TypedMap\TypedMap;

final class TestBuilder
{
    private ?string $code = null;

    private TypedMap $data;

    /**
     * @var ?\Closure(TyphoonReflector): void
     */
    private ?\Closure $test = null;

    public function __construct()
    {
        $this->data = new TypedMap();
    }

    /**
     * @return array{?string, \Closure(TyphoonReflector): void, TypedMap}
     */
    public function __invoke(): array
    {
        \assert($this->test !== null);

        return [$this->code, $this->test, $this->data];
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
     * @template T
     * @param Key<T> $key
     * @param T $value
     */
    public function value(Key $key, mixed $value): self
    {
        $this->data = $this->data->with($key, $value);

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
