<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Tests\Feature\Console\Commands;

use TypedCMS\LaravelStarterKit\Tests\TestCase;

class ConnectCommandTest extends TestCase
{
    /**
     * @test
     */
    public function itWillNotRunInProductionEnvironment(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('app.env', 'production');

        $this->artisan('typedcms:connect')->assertExitCode(1);
    }

    /**
     * @test
     */
    public function itWillRunInLocalEnvironment(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('app.env', 'local');

        $this->artisan('typedcms:connect')
            ->expectsQuestion('Please provide your OAuth Client ID', 'foo')
            ->expectsQuestion('Please provide OAuth Client Secret', 'bar')
            ->expectsQuestion('Please provide a redirect URI', 'https://mywebsite.com/display-code')
            ->expectsOutput(
                'https://app.typedcms.com/oauth/authorize?'.
                'client_id=foo&'.
                'redirect_uri=https%3A%2F%2Fmywebsite.com%2Fdisplay-code&'.
                'response_type=code&scope=delivery+access-user-data',
            )
            ->expectsQuestion('Enter the displayed authorization code here', '')
            ->expectsOutput('Verification skipped!')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function itAppendsManagementScopeWhenOptionProvided(): void
    {
        /**
         * @phpstan-ignore-next-line
         */
        $this->app->config->set('app.env', 'local');

        $this->artisan('typedcms:connect --management')
            ->expectsQuestion('Please provide your OAuth Client ID', 'foo')
            ->expectsQuestion('Please provide OAuth Client Secret', 'bar')
            ->expectsQuestion('Please provide a redirect URI', 'https://mywebsite.com/display-code')
            ->expectsOutput(
                'https://app.typedcms.com/oauth/authorize?'.
                'client_id=foo&'.
                'redirect_uri=https%3A%2F%2Fmywebsite.com%2Fdisplay-code&'.
                'response_type=code&scope=delivery+access-user-data+management',
            )
            ->expectsQuestion('Enter the displayed authorization code here', '')
            ->expectsOutput('Verification skipped!')
            ->assertExitCode(0);
    }
}
