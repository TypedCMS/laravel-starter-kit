<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Providers;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\ServiceProvider;
use kamermans\OAuth2\GrantType\AuthorizationCode;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\Persistence\FileTokenPersistence;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Swis\JsonApi\Client\Client;
use Swis\JsonApi\Client\DocumentClient;
use Swis\JsonApi\Client\Interfaces\ClientInterface;
use Swis\JsonApi\Client\Interfaces\DocumentClientInterface;
use Swis\JsonApi\Client\Interfaces\DocumentParserInterface;
use Swis\JsonApi\Client\Interfaces\ResponseParserInterface;
use Swis\JsonApi\Client\Interfaces\TypeMapperInterface;
use Swis\JsonApi\Client\Parsers\DocumentParser;
use Swis\JsonApi\Client\Parsers\ResponseParser;
use TypedCMS\LaravelStarterKit\Console\Commands\ConnectCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeModelCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeRepositoryCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeWebhooksControllerCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\MakeWebhooksHandlerCommand;
use TypedCMS\LaravelStarterKit\Console\Commands\RefreshTokenCommand;
use TypedCMS\LaravelStarterKit\Models\Resolvers\Contracts\ResolvesModels;
use TypedCMS\LaravelStarterKit\Repositories\Resolvers\Contracts\ResolvesRepositories;
use TypedCMS\LaravelStarterKit\TypeMapper;
use function app;
use function config;

class StarterKitServiceProvider extends ServiceProvider
{
    private static ?string $tokenPath = null;

    public static function setTokenPath(string $path): void
    {
        static::$tokenPath = $path;
    }

    public static function getTokenPath(): string
    {
        return (static::$tokenPath === null ? storage_path('app') : static::$tokenPath).'/token.txt';
    }

    public function register(): void
    {
        $this->mergeConfig();
        $this->registerConnectCommand();

        $this->bindHttpClient();
        $this->registerSwisJsonApi();
        $this->registerResolvers();
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
    }

    protected function bindHttpClient(): void
    {
        $this->app->bind(HttpClientInterface::class, static function (): GuzzleClient {

            $authClient = new GuzzleClient(['base_uri' => 'https://app.typedcms.com/oauth/token']);

            $authConfig = [
                'client_id' => config('typedcms.oauth.client_id'),
                'client_secret' => config('typedcms.oauth.client_secret'),
                'redirect_uri' => config('typedcms.oauth.redirect_uri'),
                'code' => config('typedcms.oauth.authorization_code'),
                'scope' => implode(' ', array_unique([...config('typedcms.oauth.scopes', []), 'access-user-data'])),
            ];

            $oauth = new OAuth2Middleware(
                new AuthorizationCode($authClient, $authConfig),
                new RefreshToken($authClient, $authConfig)
            );

            $storage = new FileTokenPersistence(static::getTokenPath());

            $oauth->setTokenPersistence($storage);

            $stack = HandlerStack::create();
            $stack->push($oauth);

            return new GuzzleClient(['auth' => 'oauth', 'handler' => $stack]);
        });
    }

    protected function registerSwisJsonApi(): void
    {
        $this->app->bind(TypeMapperInterface::class, TypeMapper::class);
        $this->app->singleton(TypeMapper::class);

        $this->app->bind(DocumentParserInterface::class, DocumentParser::class);
        $this->app->bind(ResponseParserInterface::class, ResponseParser::class);

        $this->app->bind(ClientInterface::class, Client::class);
        $this->app->bind(DocumentClientInterface::class, DocumentClient::class);
    }

    protected function registerResolvers(): void
    {
        $this->app->singleton(ResolvesModels::class, static fn () => app(config('typedcms.models.resolver')));
        $this->app->singleton(ResolvesRepositories::class, static fn () => app(config('typedcms.repositories.resolver')));
    }
}

