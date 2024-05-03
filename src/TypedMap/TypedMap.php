<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 * @psalm-immutable
 * @implements \ArrayAccess<Key, mixed>
 * @implements \IteratorAggregate<Key, mixed>
 */
final class TypedMap implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * @var array<non-empty-string, mixed>
     */
    private array $values = [];

    /**
     * @psalm-pure
     * @return non-empty-string
     */
    private static function keyToString(Key $key): string
    {
        return sprintf('%s::%s', $key::class, $key->name);
    }

    /**
     * @template T
     * @param Key<T> $key
     * @param T $value
     */
    public function with(Key $key, mixed $value): self
    {
        $copy = clone $this;
        $copy->values[self::keyToString($key)] = $value;

        return $copy;
    }

    public function withAllFrom(self $map): self
    {
        $copy = clone $this;
        $copy->values = [...$this->values, ...$map->values];

        return $copy;
    }

    public function without(Key ...$keys): self
    {
        $copy = clone $this;

        foreach ($keys as $key) {
            unset($copy->values[self::keyToString($key)]);
        }

        return $copy;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists(self::keyToString($offset), $this->values);
    }

    /**
     * @template T
     * @param Key<T> $offset
     * @return T
     */
    public function offsetGet(mixed $offset): mixed
    {
        $stringKey = self::keyToString($offset);

        if (!\array_key_exists($stringKey, $this->values)) {
            throw new UndefinedKey($offset);
        }

        /** @var T */
        return $this->values[$stringKey];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \BadMethodCallException(sprintf('%s is immutable', self::class));
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \BadMethodCallException(sprintf('%s is immutable', self::class));
    }

    /**
     * @return \Generator<Key, mixed>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->values as $key => $value) {
            $key = \constant($key);
            \assert($key instanceof Key);

            yield $key => $value;
        }
    }

    public function count(): int
    {
        return \count($this->values);
    }
}
