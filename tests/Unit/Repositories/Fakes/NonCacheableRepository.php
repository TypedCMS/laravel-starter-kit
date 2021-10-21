<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes;

use TypedCMS\LaravelStarterKit\Repositories\Repository;

class NonCacheableRepository extends Repository
{
    protected $endpoint = 'things';
}
