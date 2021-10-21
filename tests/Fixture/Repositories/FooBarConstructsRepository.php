<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class FooBarConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'foo-bar-index';

    protected string $blueprint = 'foo';

    protected array $clears = [
        BarBazConstructsRepository::class => ['react', 'delete'],
    ];
}
