<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Providers;

use Illuminate\Support\ServiceProvider;
use TypedCMS\LaravelStarterKit\Console\Commands\ConnectCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeModelCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeRepositoryCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeWebhooksControllerCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeWebhooksHandlerCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\RefreshTokenCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\ScaffoldCommand;
use TypedCMS\PHPStarterKit\StarterKit;

use function app_path;
use function array_unique;
use function config;
use function implode;

class StarterKitServiceProvider extends ServiceProvider
{
    private static ?string $tokenPath = null;

    public static function setTokenPath(string $path): void
    {
        self::$tokenPath = $path;
    }

    public static function getTokenPath(): string
    {
        return (self::$tokenPath === null ? storage_path('app') : self::$tokenPath).'/token.txt';
    }

    public static function configurePHPStarterKit(): void
    {
        StarterKit::configure([
            'base_uri' => config('typedcms.base_uri'),
            'client_id' => config('typedcms.oauth.client_id'),
            'client_secret' => config('typedcms.oauth.client_secret'),
            'redirect_uri' => config('typedcms.oauth.redirect_uri'),
            'code' => config('typedcms.oauth.authorization_code'),
            'scope' => implode(' ', array_unique([...config('typedcms.oauth.scopes', []), 'access-user-data'])),
            'token_path' => self::getTokenPath(),
            'globals_repository' => config('typedcms.globals_repo'),
            'models_path' => config('typedcms.models.resolver_path', app_path('Models')),
            'models_namespace' => config('typedcms.models.resolver_namespace', 'App\\Models'),
            'models_resolver' => config('typedcms.models.resolver'),
            'repositories_path' => config('typedcms.repositories.resolver_path', app_path('Repositories')),
            'repositories_namespace' => config('typedcms.repositories.resolver_namespace', 'App\\Repositories'),
            'repositories_resolver' => config('typedcms.repositories.resolver'),
        ]);
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->registerConnectCommand();

        static::configurePHPStarterKit();
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->loadRoutes();
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/typedcms.php', 'typedcms');
    }

    protected function publishConfig(): void
    {
        $this->publishes([dirname(__DIR__, 2).'/config/' => config_path()], 'config');
    }

    protected function LoadRoutes(): void
    {
        $this->loadRoutesFrom(dirname(__DIR__).'/Http/routes.php');
    }

    protected function registerConnectCommand(): void
    {
        $this->commands(ConnectCommand::class);
        $this->commands(MakeRepositoryCommand::class);
        $this->commands(MakeModelCommand::class);
        $this->commands(MakeWebhooksControllerCommand::class);
        $this->commands(MakeWebhooksHandlerCommand::class);
        $this->commands(RefreshTokenCommand::class);
        $this->commands(ScaffoldCommand::class);
    }
}

