<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache;

use Closure;
use TypedCMS\LaravelStarterKit\Repositories\Repository;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ClearsCacheableRepos;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ParsesCacheClears;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Handler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;
use function in_array;

class ConstructsHandler extends Handler
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
                    $traveler->addResult('Cleared Constructs Repo: '.$repo::class);
                }
            }

            $traveler->addResult(
                $cleared ? 'Constructs Cache Cleared!' : 'No cacheable constructs repositories are configured. No action taken.',
            );
        }

        return $next($traveler);
    }

    /**
     * @return array<Repository>
     */
    protected function getClearableRepositories(Traveler $traveler): array
    {
        return $this->mergePropagatedClears(
            $this->getResolver()->resolveByBlueprint($this->getBlueprint($traveler)),
            $traveler->getEvent()
        );
    }

    protected function isClearable(Traveler $traveler): bool
    {
        return in_array($traveler->getDomain(), ['blueprints', 'constructs', 'fields'], true) &&
            in_array($traveler->getEvent(), ['create', 'update', 'delete', 'react'], true);
    }

    protected function getBlueprint(Traveler $traveler): string
    {
        return $traveler->getPayload()['blueprint']['identifier'];
    }
}
