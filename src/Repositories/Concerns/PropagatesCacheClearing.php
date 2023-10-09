<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use Illuminate\Support\Arr;
use TypedCMS\LaravelStarterKit\Repositories\Repository;

use function class_exists;
use function in_array;
use function is_string;

trait PropagatesCacheClearing
{
    /**
     * @return array<class-string<Repository>>
     */
    public function getCacheClears(string $event): array
    {
        return collect($this->clears ?? [])
            ->mapWithKeys(fn (array|string $value, int|string $key): array => $this->normalise($value, $key))
            ->filter(fn (array $value): bool => $this->clearsOnEvent($event, $value))
            ->keys()
            ->all();
    }

    /**
     * @param array<string>|class-string<Repository> $value
     *
     * @return array<class-string<Repository>, array<string>>
     */
    protected function normalise(array|string $value, int|string $key): array
    {
        if ($this->isClassString($value)) {
            return [$value => ['update', 'delete', 'react']];
        }

        return [$key => Arr::wrap($value)];
    }

    /**
     * @param array<string> $value
     */
    protected function clearsOnEvent(string $event, array $value): bool
    {
        return in_array($event, $value) || in_array('*', $value);
    }

    /**
     * @param array<string>|class-string<Repository> $test
     */
    protected function isClassString(array|string $test): bool
    {
        return is_string($test) && class_exists($test);
    }
}
