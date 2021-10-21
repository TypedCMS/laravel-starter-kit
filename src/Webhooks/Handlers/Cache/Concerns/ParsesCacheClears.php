<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns;

use TypedCMS\LaravelStarterKit\Repositories\Repository;
use function app;
use function collect;

trait ParsesCacheClears
{
    /**
     * @param array<Repository> $repos
     *
     * @return array<Repository>
     */
    protected function mergePropagatedClears(array $repos, string $event): array
    {
        if (count($repos) === 0) {
            return $repos;
        }

        $parentRepos = collect($repos);
        $parentClasses = $parentRepos->map(static fn (Repository $repo): string => $repo::class);

        $childClasses = collect();

        /** @var Repository $repo */
        foreach ($parentRepos as $repo) {
            $childClasses = $childClasses->merge($repo->getCacheClears($event));
        }

        $childRepos = $childClasses
            ->unique()
            ->map(static fn (string $class): ?Repository => $parentClasses->contains($class) ? null : app($class))
            ->filter()
            ->all();

        return collect($this->mergePropagatedClears($childRepos, $event))
            ->merge($parentRepos)
            ->all();
    }
}
