<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Webhooks\Handlers;

use TypedCMS\LaravelStarterKit\Webhooks\Handlers\Contracts\HandlesWebhook;
use TypedCMS\PHPStarterKit\Repositories\Resolvers\Contracts\ResolvesRepositories;

abstract class Handler implements HandlesWebhook
{
    public function __construct(protected ResolvesRepositories $resolver) {}

    protected function getResolver(): ResolvesRepositories
    {
        return $this->resolver;
    }
}
