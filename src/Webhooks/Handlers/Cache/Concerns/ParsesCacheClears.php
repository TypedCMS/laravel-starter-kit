<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns;

use TypedCMS\PHPStarterKit\Repositories\Repository;

use function app;
use function collect;
use function in_array;

trait ParsesCacheClears
{
    /**
     * @param array<Repository> $repos
     * @param array<class-string<Repository>> $handled
     *
     * @return array<Repository>
     */
    protected function mergePropagatedClears(array $repos, string $event, array $handled = []): array
    {
        if (count($repos) === 0) {
            return $repos;
        }

        $parentRepos = collect($repos);
        $parentClasses = $parentRepos->map(static fn (Repository $repo): string => $repo::class);

        $childClasses = collect();

        foreach ($parentRepos as $repo) {

            if (method_exists($repo, 'getCacheClears')) {
                $childClasses = $childClasses->merge($repo->getCacheClears($event));
            }
        }

        $childRepos = $childClasses
            ->unique()
            ->filter(static fn (string $class) => !in_array($class, $handled))
            ->map(static fn (string $class): ?Repository => $parentClasses->contains($class) ? null : app($class))
            ->filter()
            ->all();

        $updatedHandled = $parentClasses->merge($childClasses)->unique()->all();

        return collect($this->mergePropagatedClears($childRepos, $event, $updatedHandled))
            ->merge($parentRepos)
            ->all();
    }
}
