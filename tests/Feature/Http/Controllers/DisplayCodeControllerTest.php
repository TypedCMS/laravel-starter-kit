<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Feature\Http\Controllers;

use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use TypedCMS\LaravelStarterKit\Tests\TestCase;

final class DisplayCodeControllerTest extends TestCase
{
    #[Test]
    public function itIsNotAvailableInProductionEnvironment(): void
    {
        $response = $this->get('/display-code?code=foo');

        $response->assertStatus(404);
    }

    #[Test]
    #[DefineEnvironment('localDevEnv')]
    public function itDisplaysCodeInLocalEnvironment(): void
    {
        $response = $this->get('/display-code?code=foo');

        $response->assertStatus(200);
        $response->assertSee('foo');
    }

    public function localDevEnv($app): void
    {
        $app->config->set('app.env', 'local');
    }
}
