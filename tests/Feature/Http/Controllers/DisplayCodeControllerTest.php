<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Feature\Http\Controllers;

use TypedCMS\LaravelStarterKit\Tests\TestCase;

class DisplayCodeControllerTest extends TestCase
{
    /**
     * @test
     */
    public function itIsNotAvailableInProductionEnvironment(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('app.env', 'production');

        $response = $this->get('/display-code?code=foo');

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function itDisplaysCodeInLocalEnvironment(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('app.env', 'local');

        $response = $this->get('/display-code?code=foo');

        $response->assertStatus(200);
        $response->assertSee('foo');
    }
}
