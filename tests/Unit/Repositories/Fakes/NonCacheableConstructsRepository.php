<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Unit\Repositories\Fakes;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;

class NonCacheableConstructsRepository extends ConstructsRepository
{
    protected string $collection = 'things';

    protected string $blueprint = 'thing';
}
