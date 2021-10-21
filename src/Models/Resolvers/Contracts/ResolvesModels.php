<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts;

use Swis\JsonApi\Client\Interfaces\ItemInterface;

interface ResolvesModels
{
    public function resolve(string $type): ?ItemInterface;
}
