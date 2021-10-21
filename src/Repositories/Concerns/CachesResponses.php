<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use BadMethodCallException;
use Closure;
use Illuminate\Cache\Repository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

trait CachesResponses
{
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

    protected function getCachePrefix(): ?string
    {
        return $this->cachePrefix ?? null;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function getCacheKey(string $key, array $parameters, ?string $id = null): string
    {
        $baseKey = static::class . ':';

        if ($this->isMapi()) {
            $baseKey = 'mapi:' . $baseKey;
        }

        if ($this->getCachePrefix() !== null) {
            $baseKey = $this->getCachePrefix() . ':' . $baseKey;
        }

        return $baseKey . $key . ':' . ($id !== null ? $id . ':' : '') . serialize($parameters);
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
        return $this->cacheExpiresAfter ?? (60 * 60 * 24 * 7); //7 days
    }

    protected function shouldCache(): bool
    {
        return $this instanceof Cacheable && config('typedcms.enable_caching', false);
    }

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
        /**
         * @phpstan-ignore-next-line
         */
        return app('cache')->driver($this->cacheDriver ?? null);
    }

    abstract protected function isMapi(): bool;
}
