<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;

class BarConstructsRepository extends ConstructsRepository
{
    protected string $collection = 'bar-index';

    protected string $blueprint = 'bar';
}
