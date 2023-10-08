<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories;

use TypedCMS\PHPStarterKit\Repositories\Concerns\ProvidesHierarchicalConstructs;
use TypedCMS\PHPStarterKit\Repositories\Concerns\UsesConstructsEndpoint;
use TypedCMS\PHPStarterKit\Repositories\Contracts\CollectsConstructs;

class ConstructsRepository extends Repository implements CollectsConstructs
{
    use UsesConstructsEndpoint;

    use ProvidesHierarchicalConstructs;
}
