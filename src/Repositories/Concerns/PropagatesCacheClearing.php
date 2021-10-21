<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Repositories\Concerns;

use Illuminate\Support\Arr;
use function class_exists;
use function in_array;
use function is_string;

trait PropagatesCacheClearing
{
    /**
     * @return array<class-string>
     */
    public function getCacheClears(string $event): array
    {
        return collect($this->clears ?? [])
            ->mapWithKeys(fn (string|array $value, int|string $key): array => $this->normalise($value, $key))
            ->filter(fn (string|array $value): bool => $this->clearsOnEvent($event, $value))
            ->keys()
            ->all();
    }

    /**
     * @param string|array<string> $value
     *
     * @return array<string, array<string>>
     */
    protected function normalise(string|array $value, int|string $key): array
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
     * @param array<string>|int|string $test
     */
    protected function isClassString(array|int|string $test): bool
    {
        return is_string($test) && class_exists($test);
    }
}
