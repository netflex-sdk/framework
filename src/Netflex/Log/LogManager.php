<?php

namespace Netflex\Log;

use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;

use Illuminate\Log\Logger;

class LogManager extends \Illuminate\Log\LogManager
{
  /**
   * Create an emergency log handler to avoid white screens of death.
   *
   * @return \Psr\Log\LoggerInterface
   */
  protected function createEmergencyLogger()
  {
    $config = $this->configurationFor('emergency');

    $handler = new StreamHandler(
      $config['path'] ?? $this->app->storagePath() . '/logs/netflex.log',
      $this->level(['level' => 'debug'])
    );

    return new Logger(
      new Monolog('Netflex', $this->prepareHandlers([$handler])),
      $this->app['events']
    );
  }
}
