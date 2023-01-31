<?php

namespace Netflex\Http\Facades;

use Illuminate\Support\Facades\Facade;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * @method static mixed get(string $url, bool $assoc = false)
 * @method static PromiseInterface getAsync(string $url, bool $assoc = false)
 * @method static mixed put(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static PromiseInterface putAsync(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static mixed post(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static PromiseInterface postAsync(string $url, array $payload = [], bool $assoc = false)
 * @method static mixed delete(string $url, array|null $payload = null, bool $assoc = false)
 * @method static PromiseInterface deleteAsync(string $url, bool $assoc = false)
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
