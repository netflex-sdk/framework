<?php

namespace Netflex\Http\Facades;

use Illuminate\Support\Facades\Facade;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method static mixed get(string $url, bool $assoc = false)
 * @method static PromiseInterface getAsync(string $url, bool $assoc = false)
 * @method static mixed put(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static PromiseInterface putAsync(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static mixed post(string $url, array|null  $payload = [], bool $assoc = false)
 * @method static PromiseInterface postAsync(string $url, array $payload = [], bool $assoc = false)
 * @method static mixed delete(string $url, array|null $payload = null, bool $assoc = false)
 * @method static PromiseInterface deleteAsync(string $url, array|null $payload = null, bool $assoc = false)
 * @method static mixed send(RequestInterface $request, array $options = [], $assoc = false)
 * @method static PromiseInterface sendAsync(RequestInterface $request, array $options = [], $assoc = false)
 * @method static mixed request(string $method, UriInterface|string $uri, array|null $payload = [], bool $assoc = false)
 * @method static PromiseInterface requestAsync(string $method, UriInterface|string $uri, array $options = [], bool $assoc = false)
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
