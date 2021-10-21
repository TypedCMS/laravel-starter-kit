<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers\Contracts;

use Closure;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Helpers\Traveler;

interface HandlesWebhook
{
    public function handle(Traveler $traveler, Closure $next): Closure;
}
