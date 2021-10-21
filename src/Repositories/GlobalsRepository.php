<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class GlobalsRepository extends Repository implements Cacheable
{
    /**
     * @var string
     */
    protected $endpoint = 'globals';
}
