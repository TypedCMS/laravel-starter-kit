<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use Illuminate\Cache\Repository;
use Illuminate\Support\Facades\Log;
use Throwable;
use TypedCMS\LaravelStarterKit\Jobs\RefreshCaches;

use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

use function app;
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
        $lastException = null;

        $inverse = $this->getCache()->get($this->getTrackingKey(), []);

        // @phpstan-ignore foreach.emptyArray
        foreach ($inverse as $key => $callable) {

            [$class, $method, $parameters] = unserialize($callable);

            try {

                $this->getTaggedCache()->delete($key);

                app($class)->$method(...$parameters);

                $this->getCache()->delete($this->getFlagKey());

            } catch (Throwable $e) {
                Log::error($e->getMessage(), ['exception' => $e]);
                $lastException = $e;
            }
        }

        // @phpstan-ignore notIdentical.alwaysFalse
        if ($lastException !== null) {
            throw $lastException;
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

            $inverse[$key] = serialize([static::class, $method, $parameters]);

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

            RefreshCaches::dispatch($this::class);

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
