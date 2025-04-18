<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Throwable;
use TypedCMS\LaravelStarterKit\Jobs\RefreshCaches;
use TypedCMS\LaravelStarterKit\Jobs\RefreshSingleCache;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

use function app;
use function compact;
use function method_exists;
use function serialize;
use function unserialize;

trait TracksCacheInverse
{
    public function flagForRefresh(): void
    {
        if ($this->shouldCache()) {
            $this->getCache()->forever($this->getFlagKey(), true);
        }
    }

    public function refresh(): void
    {
        $inverse = $this->getCache()->get($this->getTrackingKey(), []);

        // @phpstan-ignore foreach.emptyArray
        foreach ($inverse as $key => $callable) {

            $this->refreshOne($key, ...unserialize($callable));
        }
    }

    public function dispatchRefresh(): void
    {
        $inverse = $this->getCache()->get($this->getTrackingKey(), []);

        // @phpstan-ignore foreach.emptyArray
        foreach ($inverse as $key => $callable) {

            RefreshSingleCache::dispatch(static::class, $key, ...unserialize($callable));
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function refreshOne(string $key, string $method, array $parameters): void
    {
        try {

            $this->getTaggedCache()->delete($key);

            app(static::class)->$method(...$parameters);

            $this->getCache()->delete($this->getFlagKey());

        } catch (Throwable $e) {

            if (method_exists($this, 'handleRefreshError')) {

                $this->handleRefreshError($e, $key, $method, $parameters);

                return;
            }

            Log::error($e->getMessage(), compact('e'));
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function inverse(string $key, string $method, array $parameters): void
    {
        if (!$this->shouldCache()) {
            return;
        }

        $inverse = $this->getCache()->get($this->getTrackingKey(), []);

        // @phpstan-ignore function.impossibleType
        if (!array_key_exists($key, $inverse)) {

            $inverse[$key] = serialize([$method, $parameters]);

            $this->getCache()->forever($this->getTrackingKey(), $inverse);
        }

        $this->refreshIfFlagged();
    }

    protected function refreshIfFlagged(): void
    {
        if (
            $this instanceof Cacheable &&
            $this->getCache()->get($this->getFlagKey(), false)
        ) {

            RefreshCaches::dispatch(static::class);

            $this->getCache()->forever($this->getFlagKey(), false);
        }
    }

    protected function getTrackingKey(): string
    {
        return static::class.':inverse';
    }

    protected function getFlagKey(): string
    {
        return static::class.':inverse-flag';
    }

    abstract protected function getCache(): Repository;

    abstract protected function getTaggedCache(): Repository;

    abstract protected function shouldCache(): bool;
}
