# Netflex API

A library for working with the Netflex API.

<a href="https://packagist.org/packages/netflex/api"><img src="https://img.shields.io/packagist/v/netflex/api?label=stable" alt="Stable version"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/api.svg" alt="License: MIT"></a>
<a href="https://packagist.org/packages/netflex/api/stats"><img src="https://img.shields.io/packagist/dm/netflex/api" alt="Downloads"></a>

[Documentation](https://netflex-sdk.github.io/#/docs/api)

## Table of contents

- [Heading](#installation)
  + [Setup](#1-setup)
  + [Provider](#2-provider)
  + [Facades](#3-facades)
  + [Configuration](#4-configuration)
- [Standalone usage](#standalone-usage)
- [Testing](#testing)

Installation
------------

### 1. Setup

```shell
composer require netflex/api
```

> **Note**: If you are using [Netflex SDK](https://github.com/netflex-sdk/sdk) 2.0 or later, or Laravel 5.5, the steps 2 and 3, for providers and aliases, are unnecessaries. Netflex API supports [Package Discovery](https://laravel.com/docs/5.5/packages#package-discovery).

### 2. Provider

You need to update your application configuration in order to register the package so it can be loaded by Laravel, just update your `config/app.php` file adding the following code at the end of your `'providers'` section:

> `config/app.php`

```php
<?php

return [
    // ...
    'providers' => [
        Netflex\API\Providers\APIServiceProvider::class,
        // ...
    ],
    // ...
];
```

### 3. Facades

You may get access to the Netflex API using following facades:

 - `Netflex\API\Facades\API`

You can setup a short-version aliases for these facades in your `config/app.php` file. For example:

```php
<?php

return [
    // ...
    'aliases' => [
        'API'   => Netflex\API\Facades\API::class,
        // ...
    ],
    // ...
];
```

### 4. Configuration

#### Publish config

In your terminal type

```shell
php artisan vendor:publish
```

or

```shell
php artisan vendor:publish --provider="Netflex\API\Providers\APIServiceProvider"
```

Alternatively, you can create the config file manually, under `config/api.php`, [see example here](src/config/api.php).

Standalone usage
----------------

Netflex API can be used standalone as well.

```php
<?php

use Netflex\API\Client;

$client = new Client([
  'auth' => ['publicKey', 'privateKey']
]);
```

Testing
-------

For testing purposes, use the provided `Netflex\API\Testing\MockClient` implementation.
This can be used with the Facade, so you don't have to modify your code while testing.

To bind the MockClient into the container, register the  `Netflex\API\Testing\Providers\MockAPIServiceProvider` provider.

### Example:

```php
<?php

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;

use Netflex\API\Facades\API;
use Netflex\API\Testing\Providers\MockAPIServiceProvider;

use GuzzleHttp\Psr7\Response;

$container = new Container;

Facade::setFacadeApplication($container);

// Register the service provider
(new MockAPIServiceProvider($container))->register();

// The container binding is now registered, and you can use the Facade.

API::mockResponse(
  new Response(
    200,
    ['Content-Type' => 'application/json'],
    json_encode(['hello' => 'world'])
  )
);

$response = API::get('/'); // Returns the mocked response

echo $response->hello; // Outputs 'world'