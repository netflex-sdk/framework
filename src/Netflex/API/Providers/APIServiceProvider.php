<?php

namespace Netflex\API\Providers;

use Netflex\API\Client;
use Netflex\API\Contracts\APIClient;
use Netflex\API\APIClientResolver;
use Netflex\API\Contracts\ClientResolver;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class APIServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->alias('api.client.resolver', ClientResolver::class);
    $this->app->alias('api.client.resolver', APIClientResolver::class);

    $this->app->singleton('api.client.resolver', function () {
      return new APIClientResolver;
    });

    $this->app->alias('api.client', Client::class);
    $this->app->alias('api.client', APIClient::class);

    $this->app->singleton('api.client', function () {
      return Client::connection('default');
    });
  }

  public function boot()
  {
    $this->publishes([
      __DIR__ . '/../config/api.php' => App::configPath('api.php')
    ], 'config');
  }
}
