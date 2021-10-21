<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Contracts;

interface Cacheable
{
    public function clearCache(string ...$tags): void;
}
