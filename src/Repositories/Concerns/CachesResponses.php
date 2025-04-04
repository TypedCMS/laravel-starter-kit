<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use BadMethodCallException;
use Closure;
use Illuminate\Cache\Repository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

use function config;
use function property_exists;

trait CachesResponses
{
    private bool $skipCache = false;

    public function clearCache(string ...$tags): void
    {
        $cache = $this->getCache();

        if (count($tags) === 0) {
            $tags = $this->getCacheTags();
        }

        try {
            $cache->tags($tags)->flush();

        } catch (BadMethodCallException) {

            $cache->clear();
        }
    }

    /**
     * @return $this
     */
    public function skipCache(bool $skip = true): static
    {
        $this->skipCache = $skip;

        return $this;
    }

    protected function getCachePrefix(): ?string
    {
        return $this->cachePrefix ?? null;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function getCacheKey(string $key, array $parameters, ?string $id = null): string
    {
        $baseKey = static::class.':';

        if ($this->isMapi()) {
            $baseKey = 'mapi:'.$baseKey;
        }

        if ($this->getCachePrefix() !== null) {
            $baseKey = $this->getCachePrefix().':'.$baseKey;
        }

        return $baseKey.$key.':'.($id !== null ? $id.':' : '').serialize($parameters);
    }

    /**
     * @return array<string>
     */
    protected function getCacheTags(): array
    {
        return [static::class, ...($this->cacheTags ?? [])];
    }

    protected function getCacheExpiry(): ?int
    {
        if (property_exists($this, 'cacheExpiresAfter')) {
            return $this->cacheExpiresAfter ?: null;
        }

        return config('cache_expiration', 60 * 60 * 24 * 7); // 7 days
    }

    protected function shouldCache(): bool
    {
        return $this instanceof Cacheable &&
            config('typedcms.enable_caching', false) &&
            !$this->skipCache;
    }

    /**
     * @param Closure(): mixed $callback
     */
    protected function cache(string $key, Closure $callback): mixed
    {
        if (!$this->shouldCache()) {
            return $callback();
        }

        $cache = $this->getTaggedCache();

        if ($cache->has($key)) {
            return $cache->get($key);
        }

        if ($this->getCacheExpiry() === null) {
            return $cache->rememberForever($key, $callback);
        }

        return $cache->remember($key, $this->getCacheExpiry(), $callback);
    }

    protected function getTaggedCache(): Repository
    {
        try {
            return $this->getCache()->tags($this->getCacheTags());

        } catch (BadMethodCallException) {

            return $this->getCache();
        }
    }

    protected function getCache(): Repository
    {
        /** @phpstan-ignore-next-line */
        return app('cache')->driver($this->cacheDriver ?? null);
    }

    abstract protected function isMapi(): bool;
}
