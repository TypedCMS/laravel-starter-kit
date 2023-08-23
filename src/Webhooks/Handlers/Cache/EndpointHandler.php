<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache;

use Closure;
use TypedCMS\LaravelStarterKit\Repositories\Repository;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ClearsCacheableRepos;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ParsesCacheClears;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Handler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;

class EndpointHandler extends Handler
{
    use ClearsCacheableRepos;
    use ParsesCacheClears;

    public function handle(Traveler $traveler, Closure $next): Closure
    {
        if ($this->isClearable($traveler)) {

            $repos = $this->getClearableRepositories($traveler);

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
    protected function getClearableRepositories(Traveler $traveler): array
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
