<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class FooCLoopingConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'baz-index';

    protected string $blueprint = 'baz-loop';

    protected array $clears = [
        FooDLoopingConstructsRepository::class,
    ];
}
