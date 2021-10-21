<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Resolvers\Contracts;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Repository;

interface ResolvesRepositories
{
    /**
     * @return array<ConstructsRepository>
     */
    public function resolveByBlueprint(string $blueprint): array;

    /**
     * @return array<Repository>
     */
    public function resolveByEndpoint(string $endpoint): array;
}
