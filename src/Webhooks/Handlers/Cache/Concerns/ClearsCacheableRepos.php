<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns;

use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;
use TypedCMS\LaravelStarterKit\Repositories\Repository;

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

                $repo->clearCache();

                $cleared = true;
            }
        }

        return $cleared;
    }
}
