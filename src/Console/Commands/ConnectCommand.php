<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use kamermans\OAuth2\Exception\AccessTokenRequestException;
use kamermans\OAuth2\GrantType\AuthorizationCode;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;
use kamermans\OAuth2\Persistence\FileTokenPersistence;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;

final class ConnectCommand extends Command
{
    protected $signature = 'typedcms:connect {--management}';

    protected $description = 'Authorise a TypedCMS OAuth application';

    public function handle(): int
    {
        if (config('app.env') !== 'local') {

            $this->error('This command is only available in your local environment!');

            return 1;
        }

        $scope = 'delivery access-user-data';

        if ($this->option('management')) {
            $scope .= ' management';
        }

        $id = $this->anticipate('Please provide your OAuth Client ID', [
            config('typedcms.oauth.client_id'),
        ], config('typedcms.oauth.client_id'));

        $secret = $this->anticipate('Please provide OAuth Client Secret', [
            config('typedcms.oauth.client_secret'),
        ], config('typedcms.oauth.client_secret'));

        $uri = $this->anticipate('Please provide a redirect URI', [
            config('typedcms.oauth.redirect_uri'),
            config('app.url').'/display-code',
        ], config('typedcms.oauth.redirect_uri'));

        $query = http_build_query([
            'client_id' => $id,
            'redirect_uri' => $uri,
            'response_type' => 'code',
            'scope' => $scope,
        ]);

        $this->newLine(2);

        $this->info('Please click authorise at the following link:');

        $this->line('https://app.typedcms.com/oauth/authorize?'.$query);

        $code = $this->ask('Enter the displayed authorization code here');

        if ($code === '' || $code === null) {

            $this->info('Verification skipped!');

            return 0;
        }

        $authClient = new Client(['base_uri' => 'https://app.typedcms.com/oauth/token']);

        $authConfig = [
            'client_id' => $id,
            'client_secret' => $secret,
            'redirect_uri' => $uri,
            'code' => $code,
            'scope' => $scope,
        ];

        $oauth = new OAuth2Middleware(
            new AuthorizationCode($authClient, $authConfig),
            new RefreshToken($authClient, $authConfig),
        );

        $storage = new FileTokenPersistence(StarterKitServiceProvider::getTokenPath());

        $oauth->setTokenPersistence($storage);

        try {

            $oauth->getAccessToken();

        } catch (AccessTokenRequestException) {

            $this->error('Failed to verify access token!');

            return 1;
        }

        $this->info('Access token verified!');

        $this->warn('Please add the authorization code to your config:');

        $this->line($code);

        return 0;
    }
}
