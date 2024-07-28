<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class FooBLoopingConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'bar-index';

    protected string $blueprint = 'bar-loop';

    protected array $clears = [
        FooCLoopingConstructsRepository::class,
    ];
}
