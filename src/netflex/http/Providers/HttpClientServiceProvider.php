<?php

namespace Netflex\Http\Providers;

use Netflex\Http\Client;
use Netflex\Http\Contracts\HttpClient;

use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->app->alias('netflex.http.client', Client::class);
    $this->app->alias('netflex.http.client', HttpClient::class);

    $this->app->singleton('netflex.http.client', function () {
      return new Client();
    });
  }
}
