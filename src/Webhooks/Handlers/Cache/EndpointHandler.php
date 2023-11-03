<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache;

use Closure;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ClearsCacheableRepos;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\CollectsCacheClears;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Handler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;
use TypedCMS\PHPStarterKit\Repositories\Repository;

class EndpointHandler extends Handler
{
    use ClearsCacheableRepos;
    use CollectsCacheClears;

    public function handle(Traveler $traveler, Closure $next): Closure
    {
        if ($this->isClearable($traveler)) {

            $repos = $this->getRepositories($traveler);

            $cleared = $this->clearCaches($repos);

            if ($cleared) {

                foreach ($repos as $repo) {
                    $traveler->addResult('Cleared Repo: '.$repo::class);
                }

                $traveler->addResult('Other Caches Cleared!');
            }
        }

        return $next($traveler);
    }

    /**
     * @return array<Repository>
     */
    protected function getRepositories(Traveler $traveler): array
    {
        return $this->mergePropagatedClears(
            $this->getResolver()->resolveByEndpoint($this->getEndpoint($traveler)),
            $traveler->getEvent()
        );
    }

    protected function isClearable(Traveler $traveler): bool
    {
        return $traveler->getDomain() !== 'globals';
    }

    protected function getEndpoint(Traveler $traveler): string
    {
        return $traveler->getDomain();
    }
}
