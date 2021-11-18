<?php

namespace Netflex\API;

use Netflex\Http\Client as HttpClient;

use Netflex\API\Contracts\APIClient;
use Netflex\API\Exceptions\MissingCredentialsException;
use Netflex\API\Facades\APIClientConnectionResolver;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Traits\Macroable;

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
