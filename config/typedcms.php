<?php

return [

    /*
    |---------------------------------------------------------------------------
    | API Base URI
    |---------------------------------------------------------------------------
    |
    | Specify the base URI for your TypedCMS project. You should not include the
    | host (https://api.tcms.io/ or https://mapi.tcms.io/) as this will be
    | automatically prepended for you. Just your team and project identifiers
    | are required formatted as @team/project.
    |
    */
    'base_uri' => env('BASE_URI', '@team/project'),

    /*
    |---------------------------------------------------------------------------
    | TypedCMS OAuth Credentials
    |---------------------------------------------------------------------------
    |
    | You can configure your OAuth credentials here. Use the `php artisan
    | typedcms:connect` command to generate an authorization code.
    |
    | The redirect URI should point to the `/display-code` route if you are
    | using the connect command. This command and route are only available in
    | your local environment.
    |
    | Remember your authorisation code will be specific to your user account,
    | not a team or project.
    |
    | You should never share these credentials with anyone!
    |
    */
    'oauth' => [

        'client_id' => env('OAUTH_CLIENT_ID', ''),

        'client_secret' => env('OAUTH_CLIENT_SECRET', ''),

        'redirect_uri' => env('OAUTH_REDIRECT_URI', 'http://127.0.0.1:8000/display-code'),

        'authorization_code' => env('OAUTH_AUTHORIZATION_CODE', ''),

        'scopes' => ['delivery'],
    ],

    /*
    |---------------------------------------------------------------------------
    | Enable Repository Caching
    |---------------------------------------------------------------------------
    |
    | The starter kit includes basic caching for your repositories. Any
    | repositories you want to be cached should implement the `Cacheable`
    | interface.
    |
    | This option allows you to globally switch caching on or off. This can be
    | useful in development, for example.
    |
    | Make sure you configure a webhook that triggers on construct, global or
    | field for the create, update, delete, and react events. It should point to
    | the included `/webhooks/clear-cache` route.
    |
    */
    'enable_caching' => env('ENABLE_CACHING', true),

    /*
    |---------------------------------------------------------------------------
    | Webhook Secrets
    |---------------------------------------------------------------------------
    |
    | In order to protect your application from fraudulent requests, TypedCMS
    | signs all webhooks with a secret. If you're using the `cache-clear`
    | webhook, you'll need to set the `cache` webhook secret here.
    |
    | This is also a handy little place to store any other webhook secrets your
    | application may need.
    |
    */
    'webhook_secrets' => [
        'cache' => env('CACHE_WEBHOOK_SECRET', ''),
    ],

    /*
    |---------------------------------------------------------------------------
    | Webhook Handlers
    |---------------------------------------------------------------------------
    |
    | Add any additional webhook handlers to be appended to your named webhooks
    | pipelines.
    |
    */
    'webhook_handlers' => [
        'cache' => [
            //GenerateSitemap::class,
        ],
    ],

    /*
    |---------------------------------------------------------------------------
    | Globals Repository
    |---------------------------------------------------------------------------
    |
    | A `Cacheable` globals repository is provided by this package. If you wish
    | to extend/replace this repository be sure to update the class name here.
    |
    | Note: This repository is cacheable by default. If you are not intending to
    | utilise the provided caching mechanism, you should ensure `enable_caching`
    | is set to `false`.
    |
    */
    'globals_repo' => TypedCMS\LaravelStarterKit\Repositories\GlobalsRepository::class,

    /*
    |---------------------------------------------------------------------------
    | Repositories Resolver
    |---------------------------------------------------------------------------
    |
    | Occasionally we need to locate all the repositories in your application
    | that are for a specific blueprint or endpoint, such as when clearing
    | caches.
    |
    | The provided basic resolver scans your `App/Repositories` directory. If
    | you need a different directory structure you can provide your own resolver
    | that implements the `ResolvesRepositories` interface here.
    |
    */
    'repositories' => [
        'resolver' => TypedCMS\PHPStarterKit\Repositories\Resolvers\BasicResolver::class,
    ],

    /*
    |---------------------------------------------------------------------------
    | Models Resolver
    |---------------------------------------------------------------------------
    |
    | Response data is parsed into Eloquent like models. By default, a generic
    | `Item` model is used. This resolver will locate a specific model for a
    | given resource type.
    |
    | All constructs use the JSON:API resource type `construct`. As a result we
    | resolve all constructs to the provided `Construct` model. This model has a
    | `specialise` method which will resolve a specialised construct model using
    | this resolver. Custom construct models must extend the base `Construct`
    | model.
    |
    | The provided basic resolver scans your `App/Models` directory. If you need
    | a different directory structure you can provide your own resolver
    | that implements the ResolvesModels interface here.
    |
    */
    'models' => [
        'resolver' => TypedCMS\PHPStarterKit\Models\Resolvers\BasicResolver::class,
    ],
];

