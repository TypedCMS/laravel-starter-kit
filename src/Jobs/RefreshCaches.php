<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use function app;
use function config;

class RefreshCaches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(private readonly string $repoClass) {}

    public function handle(): void
    {
        if (config('typedcms.granular_cache_queueing')) {

            app($this->repoClass)->dispatchRefresh();

            return;
        }

        app($this->repoClass)->refresh();
    }
}
