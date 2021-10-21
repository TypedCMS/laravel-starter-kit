<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes;

use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;
use TypedCMS\LaravelStarterKit\Repositories\Repository;

class CacheableRepository extends Repository implements Cacheable
{
    protected $endpoint = 'things';
}
