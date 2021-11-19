<?php

namespace Netflex\MessageChannel\Providers;

use Netflex\MessageChannel\Handler;
use Netflex\MessageChannel\Client as MessageChannel;

use Illuminate\Support\ServiceProvider;

class MessageChannelServiceProvider extends ServiceProvider
{
  public function boot()
  {
    $this->publishes([
      __DIR__ . '/config/media.php' => $this->app->configPath('message-channel.php')
    ], 'config');
  }

  public function register()
  {
    $this->app->singleton(MessageChannel::class, function () {
      $publicKey = $this->app['config']['api.publicKey'] ?? null;
      $privateKey = $this->app['config']['api.privateKey'] ?? null;
      $handler = null;

      if ($endpoint = $this->app['config']['message-channel.handler.endpoint']) {
        $method = $this->app['config']['message-channel.handler.method'] ?? 'post';
        $handler = new Handler($endpoint, $method);
      }

      $baseURI = $this->app['config']['message-channel.baseURI'] ?? 'https://broadcast.netflexapp.com';
      $prefixWithChannel = $this->app['config']['message-channel.prefixChannel'] ?? true;

      return new MessageChannel($publicKey, $privateKey, $handler, $baseURI, $prefixWithChannel);
    });
  }
}
