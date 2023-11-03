<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;
use TypedCMS\LaravelStarterKit\Repositories\GlobalsRepository;

class FooDLoopingConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'foobar-index';

    protected string $blueprint = 'foobar-loop';

    protected array $clears = [
        FooELoopingConstructsRepository::class,
    ];
}
