<?php

namespace Netflex\API;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Traits\Macroable;
use Netflex\API\Contracts\APIClient;
use Netflex\API\Events\RequestFailed;
use Netflex\API\Events\RequestStarted;
use Netflex\API\Events\RequestSucceeded;
use Netflex\API\Exceptions\MissingCredentialsException;
use Netflex\API\Facades\APIClientConnectionResolver;
use Netflex\Http\Client as HttpClient;
use Psr\Http\Message\RequestInterface;

class Client extends HttpClient implements APIClient
{
  use Macroable;

  protected $connection = 'default';

  /**
   * @return string|null
   */
  public function getConnectionName()
  {
    return $this->connection;
  }

  /**
   * @param string|null $connection
   * @return static
   */
  public function setConnectionName($connection)
  {
    $this->connection = $connection;
    return $this;
  }

  /** @var String */
  const BASE_URI = 'https://api.netflexapp.com/v1/';

  public static function connection($connection = 'default')
  {
    return APIClientConnectionResolver::resolve($connection);
  }

  public static function withCredentials($credentials)
  {
    return new static([
      'auth' => $credentials
    ]);
  }

  /**
   * @param array $options
   */
  public function __construct(array $options = [])
  {
    parent::__construct($options);
    $this->setCredentials($options);
  }

  public function setCredentials(array $options = [])
  {
    $options['base_uri'] = $options['base_uri'] ?? static::BASE_URI;
    $options['auth'] = $options['auth'] ?? null;

    if (!$options['auth']) {
      throw new MissingCredentialsException;
    }



    if(!isset($options['handler'])) {
      $options['handler'] = HandlerStack::create();
    }

    $exposeEvents = function (callable $handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        $requestId = $options['request-id'] ?? uuid();
        $request = $request->withHeader('X-Request-Id', $requestId);

        try {
          event(new RequestStarted(clone $request));
          $response = $handler($request, $options);
          event(new RequestSucceeded(clone $request, $response));
          return $response;
        } catch (\Throwable $t) {
          event(new RequestFailed(clone $request, $t));
          return $response;
        }
      };
    };

    $options['handler']->push($exposeEvents);


    $this->client = new GuzzleClient($options);

    return $this->client;
  }

  /**
   * Returns the raw internal Guzzle instance
   *
   * @return GuzzleClient
   */
  public function getGuzzleInstance()
  {
    return $this->client;
  }
}
