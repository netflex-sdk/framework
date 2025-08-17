<?php

namespace Netflex\Notifications\Providers;

use Illuminate\Mail\MailServiceProvider as ServiceProvider;
use Illuminate\Notifications\ChannelManager;

use Netflex\Notifications\Channels\SMS;
use Netflex\Notifications\Transport\NotificationsTransport;

class NotificationsServiceProvider extends ServiceProvider
{
  public function boot()
  {
    if ($this->app->has(ChannelManager::class)) {
      $this->app->make(ChannelManager::class)
        ->extend('sms', function () {
          return new SMS;
        });
    }

    if ($this->app->has('mail.manager')) {
      $this->app->make('mail.manager')
        ->extend('netflex', function () {
          return new NotificationsTransport;
        });
    }
  }
}
