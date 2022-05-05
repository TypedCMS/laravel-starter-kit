<?php

declare(strict_types=1);

namespace TypedCMS\LaravelStarterKit\Console\Commands;

use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\Persistence\FileTokenPersistence;
use kamermans\OAuth2\Signer\ClientCredentials\BasicAuth;
use kamermans\OAuth2\Token\RawToken;
use kamermans\OAuth2\Token\RawTokenFactory;
use TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider;

use function array_unique;
use function config;
use function implode;

final class RefreshTokenCommand extends Command
{
    protected $signature = 'typedcms:refresh {--expiring}';

    protected $description = 'Refresh a TypedCMS access token';

    public function handle(): int
    {
        $tokenPersistence = new FileTokenPersistence(StarterKitServiceProvider::getTokenPath());

        /** @var RawToken|null $rawToken */
        $rawToken = $tokenPersistence->restoreToken(new RawToken());

        if ($rawToken === null) {

            $this->error('Unable to retrieve existing access token.');

            return 1;
        }

        if (
            $this->option('expiring') &&
            Carbon::make((new DateTime())->setTimestamp($rawToken->getExpiresAt()))->isAfter(Carbon::now()->addWeek())
        ) {

            $this->info('Access token expires more than 7 days from now.');

            return 0;
        }

        $authClient = new GuzzleClient(['base_uri' => 'https://app.typedcms.com/oauth/token']);

        $authConfig = [
            'client_id' => config('typedcms.oauth.client_id'),
            'client_secret' => config('typedcms.oauth.client_secret'),
            'redirect_uri' => config('typedcms.oauth.redirect_uri'),
            'code' => config('typedcms.oauth.authorization_code'),
            'scope' => implode(' ', array_unique([...config('typedcms.oauth.scopes', []), 'access-user-data'])),
        ];

        try {
            $rawData = (new RefreshToken($authClient, $authConfig))
                ->getRawData(new BasicAuth(), $rawToken->getRefreshToken());

        } catch (BadResponseException) {

            $this->error('Unable to request a new access token.');

            return 1;
        }

        $tokenPersistence->saveToken((new RawTokenFactory())($rawData));

        $this->info('Access token refreshed!');

        return 0;
    }
}
