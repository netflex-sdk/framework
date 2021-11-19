<?php

namespace Netflex\Http\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $url, bool $assoc = false)
 * @method static mixed put(string $url, array $payload = [], bool $assoc = false)
 * @method static mixed post(string $url, array $payload = [], bool $assoc = false)
 * @method static mixed delete(string $url, bool $assoc = false)
 *
 * @see \Netflex\Http\Client
 */
class Http extends Facade
{
  /**
   * Get the registered name of the component.
   *
   * @return string
   */
  protected static function getFacadeAccessor()
  {
    return 'netflex.http.client';
  }
}
