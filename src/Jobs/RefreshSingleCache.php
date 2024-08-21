<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use function app;

class RefreshSingleCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly string $repoClass,
        private readonly string $key,
        private readonly string $methods,
        private readonly array $parameters,
    ) {
    }

    public function handle(): void
    {
        app($this->repoClass)->refreshOne($this->key, $this->methods, $this->parameters);
    }
}
