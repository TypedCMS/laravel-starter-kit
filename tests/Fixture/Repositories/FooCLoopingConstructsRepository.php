<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;

class FooCLoopingConstructsRepository extends ConstructsRepository
{
    protected string $collection = 'foo-index';

    protected string $blueprint = 'foo-loop';

    protected array $clears = [
        FooALoopingConstructsRepository::class,
    ];
}
