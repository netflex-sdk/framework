<?php

namespace Netflex\Log;

use Netflex\Log\LogManager;

use Illuminate\Log\LogServiceProvider as ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->app->singleton('log', function () {
      return new LogManager($this->app);
    });
  }
}
