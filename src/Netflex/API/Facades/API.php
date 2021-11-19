<?php

namespace Netflex\API\Facades;

use Netflex\Http\Facades\Http as Facade;

/**
 * @method static mixed setCredentials(array $options)
 * @method static \Netflex\API\Contracts\APIClient connection(string $connection)
 * @method static \Netflex\API\Contracts\APIClient withCredentials(array $credentials)
 *
 * @see \Netflex\API\Client
 */
class API extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'api.client';
  }
}
