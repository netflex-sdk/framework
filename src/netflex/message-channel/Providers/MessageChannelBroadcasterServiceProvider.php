<?php

namespace Netflex\MessageChannel\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

use Netflex\MessageChannel\Broadcaster as MessageChannelBroadcaster;

class MessageChannelBroadcasterServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   */
  public function boot()
  {
    Broadcast::extend('netflex', function () {
      return new MessageChannelBroadcaster;
    });
  }
}
