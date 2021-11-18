<?php

namespace Netflex\API\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Netflex\API\Contracts\APIClient resolve(string $connection)
 *
 * @see \Netflex\API\Client
 */
class APIClientConnectionResolver extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'api.client.resolver';
  }
}
