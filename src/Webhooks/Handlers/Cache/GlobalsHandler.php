<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache;

use Closure;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;
use TypedCMS\LaravelStarterKit\Repositories\Repository;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ClearsCacheableRepos;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\Concerns\ParsesCacheClears;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Handler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;

use function app;
use function config;
use function in_array;

class GlobalsHandler extends Handler
{
    use ClearsCacheableRepos;
    use ParsesCacheClears;

    public function handle(Traveler $traveler, Closure $next): Closure
    {
        if ($this->isClearable($traveler)) {

            $repo = $this->getRepository();

            if ($repo instanceof Cacheable) {

                $this->clearCaches($this->mergePropagatedClears([$repo], $traveler->getEvent()));

                $traveler->addResult('Globals Cache Cleared!');

            } else {
                $traveler->addResult('Custom globals repository is not cacheable. No action taken.');
            }
        }

        return $next($traveler);
    }

    protected function isClearable(Traveler $traveler): bool
    {
        return $traveler->getDomain() === 'globals' &&
            in_array($traveler->getEvent(), ['create', 'update', 'delete', 'react'], true);
    }

    protected function getRepository(): Repository
    {
        return app(config('typedcms.globals_repo'));
    }
}
