<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns;

use TypedCMS\LaravelStarterKit\Jobs\RefreshCaches;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;
use TypedCMS\PHPStarterKit\Repositories\Repository;

use function config;

trait ClearsCacheableRepos
{
    /**
     * @param array<Repository> $repos
     */
    protected function clearCaches(array $repos): bool
    {
        $cleared = false;

        foreach ($repos as $repo) {

            if ($repo instanceof Cacheable) {

                $this->clearWithStrategy($repo);

                $cleared = true;
            }
        }

        return $cleared;
    }

    protected function clearWithStrategy(Cacheable $repo): void
    {
        match (config('typedcms.cache_strategy')) {
            'eager' => RefreshCaches::dispatch($repo::class),
            'async' => $repo->flagForRefresh(),
            default => $repo->clearCache(),
        };
    }
}
