# TypedCMS Starter Kit for Laravel

Our stater kits are tailored solutions for each platform, unlike the simple API 
wrappers offered by other vendors. The [TypedCMS](https://typedcms.com) starter 
kit for Laravel provides Eloquent like models, repositories, query caching and a 
simple mechanism for adding custom webhook controllers and handlers.

This starter kit wouldn't be possible without the hard work of the many open 
source package developers and contributors on which it is built.

## Requirements

* PHP 8+
* Laravel 8+

## Installation

First, install the package via the [Composer](https://getcomposer.org);

```bash
composer require typedcms/laravel-starter-kit
```

This package will automatically register its service provider.

To publish the config file please run:

```bash
php artisan vendor:publish --provider="TypedCMS\LaravelStarterKit\Providers\StarterKitServiceProvider" 
```

## Connecting to TypedCMS

To connect, you'll need an OAuth client application registered in TypedCMS. 

Open your TypedCMS config file and set the `client_id` and `client_secret` to 
match your client application in TypedCMS. The `redirect_uri` should point to 
the `display-code` route provided by this package. If you’re using 
`php artisan serve`, this will be something like 
`http://localhost:8000/display-code`. If you need access to the management API,
be sure to update the scope too. Leave the `authorization_code` for now.

Now run:

```bash
php artisan typedcms:connect
```

You’ll be 
asked for your client credentials, however you should see that they have been 
prefilled, so you can just press enter to skip through these until you’re given 
an authorization URL. Open this link in your browser and click the Authorize 
button, ensuring you're authorising the correct account. 
Copy the code displayed on the screen (Ctr+A then Ctr+C to make sure you get it 
all). Paste this code into the terminal where prompted. If it is verified 
successfully, you can now paste this code into the `authorization_code` property 
of your config file.

**Be careful here as you're authorising access to all teams and projects that 
the authorised account has access to!**

That's it! You're now connected to TypedCMS.

## Usage

### Repositories

To create a repository for a specific endpoint run:

```bash
php artisan typedcms:make:repository MyRepo -eendpoint
```

Or for a specified blueprint and collection:

```bash
php artisan typedcms:make:repository MyRepo -b'blueprint-name' -c'collection-name'
```

The repositories will be your primary means of interacting with the TypedCMS 
API. This functionality is built on the incredible
[{ json:api } Client](https://github.com/swisnl/json-api-client) package by 
[SWIS](https://www.swis.nl). For usage documentation please refer to
[their documentation](https://github.com/swisnl/json-api-client).

Generated repositories will be placed in the `app/Repositories` directory of your
application.

A generated repository will look something like this:

```php
<?php

declare(strict_types=1);

namespace App\Repositories;

use TypedCMS\LaravelStarterKit\Repositories\ConstructsRepository;
use TypedCMS\LaravelStarterKit\Repositories\Contracts\Cacheable;

class MyRepo extends ConstructsRepository implements Cacheable
{
    protected string $collection = 'collection-name';

    protected string $blueprint = 'blueprint-name';

    /**
     * When this repository's cache is cleared, repositories listed here will
     * also be cleared.
     */
    protected array $clears = [];

    /**
     * By default, repositories make requests to the delivery api. Set this to
     * true if you wish to use the management api by default.
     */
    protected bool $mapi = false;
}
```

By default, all responses are cached. If you would like to disable caching for a
repository you can remove its `Cacheable` interface.

If your model only interacts with the management API, you can set its 
`$mapi` property to true. You can also switch any query to the management API 
fluidly with the `mapi()` method like so:


```php
$myRepo->mapi()->all();
```

### Models

To create a model for a specific resource type run:

```bash
php artisan typedcms:make:model MyModel -t'resource-name'
```

Or for a specified blueprint:

```bash
php artisan typedcms:make:model MyModel -b'blueprint-name'
```

Generated models will be placed in the `app/Models` directory of your
application.

These are eloquent-like are built on the fantastic
[jenssegers/model](https://github.com/jenssegers/model) package.

### Resolvers

Repositories and models are automatically resolved using their respective 
resolvers. If you need to implement a different file structure, you can 
implement your own resolvers.
