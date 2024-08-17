<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class RefreshCaches implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Cacheable $repo)
    {
    }

    public function handle(): void
    {
        $this->repo->refresh();
    }
}
