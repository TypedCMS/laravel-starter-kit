<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Contracts;

interface Cacheable
{
    public function clearCache(string ...$tags): void;

    /**
     * @return array<class-string<Cacheable>>
     */
    public function getCacheClears(string $event): array;

    public function flagForRefresh(): void;

    public function refresh(): void;
}
