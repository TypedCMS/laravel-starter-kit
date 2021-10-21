<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class BarBazConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'bar-baz-index';

    protected string $blueprint = 'bar';

    protected array $clears = [
        BarConstructsRepository::class => ['delete'],
        FooBarConstructsRepository::class,
    ];
}
