<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class FooCLoopingConstructsRepository extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'foo-index';

    protected string $blueprint = 'foo-loop';

    protected array $clears = [
        FooALoopingConstructsRepository::class,
    ];
}
