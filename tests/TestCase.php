<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests;

use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [StarterKitServiceProvider::class];
    }
}
