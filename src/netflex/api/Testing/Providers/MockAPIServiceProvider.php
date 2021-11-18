<?php

namespace Netflex\API\Testing\Providers;

use Netflex\API\Testing\MockClient;
use Netflex\API\Contracts\APIClient;

use Illuminate\Support\ServiceProvider;

class MockAPIServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->alias('api.client', MockClient::class);
    $this->app->alias('api.client', APIClient::class);

    $this->app->singleton('api.client', function () {
      return new MockClient();
    });
  }
}
