<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Http\Controllers;

use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\ConstructsHandler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\EndpointHandler;
use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Cache\GlobalsHandler;

class ClearCacheController extends WebhooksController
{
    protected string $name = 'cache';

    protected array $handlers = [
        GlobalsHandler::class,
        ConstructsHandler::class,
        EndpointHandler::class,
    ];
}
