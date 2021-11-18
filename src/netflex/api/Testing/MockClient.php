<?php

namespace Netflex\API\Testing;

use Netflex\API\Contracts\APIClient;
use Netflex\Http\Concerns\ParsesResponse;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException as Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Traits\Macroable;

class MockClient implements APIClient
{
  use ParsesResponse;
  use Macroable;

  /** @var Client */
  protected $client;

  /** @var MockHandler */
  protected $mock;

  /** @var HandlerStack */
  protected $stack;

  protected $connection = 'mock';

  public function __construct()
  {
    $this->mock = new MockHandler();
    $this->stack = HandlerStack::create($this->mock);
    $this->client = new GuzzleClient(['handler' => $this->stack]);
  }

  /**
   * @return string|null
   */
  public function getConnectionName ()
  {
    return $this->connection;
  }

  /**
   * @param string|null $connection
   * @return static
   */
  public function setConnectionName ($connection)
  {
    $this->connection = $connection;
    return $this;
  }

  /**
   * Adds a Response to the mock queue
   *
   * @param Response $response
   * @return void
   */
  public function mockResponse(Response $response)
  {
    $this->mock->append($response);
  }

  /**
   * Adds a RequestException to the mock queue
   *
   * @param RequestException $e
   * @return void
   */
  public function mockRequestException(RequestException $e)
  {
    $this->mock->append($e);
  }

  /**
   * Resets the mock queue
   *
   * @return void
   */
  public function reset()
  {
    $this->mock->reset();
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function get($url, $assoc = false)
  {
    return $this->parseResponse(
      $this->client->get($url),
      $assoc
    );
  }

  /**
   * @param string $url
   * @param array $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function put($url, $payload = [], $assoc = false)
  {
    return $this->parseResponse(
      $this->client->put($url, ['json' => $payload]),
      $assoc
    );
  }

  /**
   * @param string $url
   * @param array $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function post($url, $payload = [], $assoc = false)
  {
    return $this->parseResponse(
      $this->client->post($url, ['json' => $payload]),
      $assoc
    );
  }

  /**
   * @param string $url
   * @return mixed
   * @throws Exception
   */
  public function delete($url, $assoc = false)
  {
    return $this->parseResponse(
      $this->client->delete($url),
      $assoc
    );
  }
}
