<?php

declare(strict_types=1);

namespace Typhoon\Reflection\Internal\Storage;

use Psr\SimpleCache\CacheInterface;
use Typhoon\DeclarationId\DeclarationId;
use Typhoon\TypedMap\TypedMap;

/**
 * @internal
 * @psalm-internal Typhoon\Reflection
 */
final class DataStorage
{
    /**
     * @var array<non-empty-string, DataCacheItem>
     */
    private array $deferred = [];

    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    /**
     * @return non-empty-string
     */
    private static function key(DeclarationId $id): string
    {
        return hash('xxh128', $id->toString());
    }

    public function get(DeclarationId $id): ?TypedMap
    {
        $key = self::key($id);

        if (isset($this->deferred[$key])) {
            return $this->deferred[$key]->get();
        }

        $data = $this->cache->get($key);

        if ($data instanceof DataCacheItem) {
            return $data->get();
        }

        return null;
    }

    /**
     * @param \Closure(): TypedMap $data
     */
    public function stageForCommit(DeclarationId $id, \Closure $data): void
    {
        $this->deferred[self::key($id)] = new DataCacheItem($data);
    }

    public function commit(): void
    {
        if ($this->deferred === []) {
            return;
        }

        $this->cache->setMultiple($this->deferred);
        $this->deferred = [];
    }

    public function rollback(): void
    {
        $this->deferred = [];
    }
}