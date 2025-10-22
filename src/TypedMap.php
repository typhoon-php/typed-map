<?php

declare(strict_types=1);

namespace Typhoon\TypedMap;

/**
 * @api
 * @implements \ArrayAccess<Key, mixed>
 */
final class TypedMap implements \ArrayAccess, \Countable
{
    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    /**
     * @template TValue
     * @param Key<TValue> $key
     * @param TValue $value
     */
    public static function one(Key $key, mixed $value): self
    {
        $map = new self();
        $map->values[serialize($key)] = $value;

        return $map;
    }

    /**
     * @template TValue
     * @param Key<TValue> $key
     * @param TValue $value
     */
    public function with(Key $key, mixed $value): self
    {
        $copy = clone $this;
        $copy->values[serialize($key)] = $value;

        return $copy;
    }

    public function withMap(self $map): self
    {
        $copy = clone $map;
        $copy->values += $this->values;

        return $copy;
    }

    public function without(Key ...$keys): self
    {
        $copy = clone $this;

        foreach ($keys as $key) {
            unset($copy->values[serialize($key)]);
        }

        return $copy;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->values[serialize($offset)]);
    }

    /**
     * @template TValue
     * @param Key<TValue> $offset
     * @return TValue
     * @throws KeyIsNotDefined
     */
    public function offsetGet(mixed $offset): mixed
    {
        $key = serialize($offset);

        if (\array_key_exists($key, $this->values)) {
            /** @var TValue */
            return $this->values[$key];
        }

        if ($offset instanceof OptionalKey) {
            /** @var OptionalKey<TValue> $offset */
            return $offset->default($this);
        }

        throw new KeyIsNotDefined($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \BadMethodCallException(\sprintf('%s is immutable', self::class));
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \BadMethodCallException(\sprintf('%s is immutable', self::class));
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return $this->values;
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            \assert(\is_string($key) && unserialize($key) instanceof Key);
            $this->values[$key] = $value;
        }
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return \count($this->values);
    }
}
