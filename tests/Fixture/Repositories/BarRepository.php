<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Fixture\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\Repository;

class BarRepository extends Repository
{
    protected $endpoint = 'bars';
}
