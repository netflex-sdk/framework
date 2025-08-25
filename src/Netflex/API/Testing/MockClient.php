<?php

namespace Netflex\API\Testing;

use Netflex\API\Contracts\APIClient;
use Netflex\Http\Concerns\ParsesResponse;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException as Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Traits\Macroable;
use Psr\Http\Message\RequestInterface;

class MockClient implements APIClient
{
  use ParsesResponse;
  use Macroable;

  protected Client $client;

  protected MockHandler $mock;

  protected HandlerStack $stack;

  protected string $connectionName = 'mock';

  public function __construct()
  {
    $this->mock = new MockHandler();
    $this->stack = HandlerStack::create($this->mock);
    $this->client = new Client(['handler' => $this->stack]);
  }

  /**
   * @return string|null
   */
  public function getConnectionName(): ?string
  {
    return $this->connectionName;
  }

  /**
   * @param string|null $name
   * @return static
   */
  public function setConnectionName(?string $name): static
  {
    $this->connectionName = $name;

    return $this;
  }

  /**
   * Adds a Response to the mock queue
   *
   * @param Response $response
   * @return void
   */
  public function mockResponse(Response $response): void
  {
    $this->mock->append($response);
  }

  /**
   * Adds a RequestException to the mock queue
   *
   * @param RequestException $e
   * @return void
   */
  public function mockRequestException(RequestException $e): void
  {
    $this->mock->append($e);
  }

  /**
   * Resets the mock queue
   *
   * @return void
   */
  public function reset(): void
  {
    $this->mock->reset();
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function get(string $url, bool $assoc = false): mixed
  {
    return $this->parseResponse(
      $this->client->get($url),
      $assoc,
    );
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function put(
    string $url,
    array|null $payload = [],
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse(
      $this->client->put($url, ['json' => $payload]),
      $assoc,
    );
  }

  /**
   * @param string $url
   * @param array $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function post(string $url, $payload = [], bool $assoc = false): mixed
  {
    return $this->parseResponse(
      $this->client->post($url, ['json' => $payload]),
      $assoc,
    );
  }

  /**
   * @param string $url
   * @param array|null $payload
   * @param bool $assoc
   * @return mixed
   * @throws Exception
   */
  public function delete(
    string $url,
    array|null $payload = null,
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse(
      $this->client->delete($url),
      $assoc,
    );
  }

  public function send(
    RequestInterface $request,
    array $options = [],
    bool $assoc = false,
  ) {
    return $this->parseResponse(
      $this->client->send($request, $options),
      $assoc,
    );
  }
}
