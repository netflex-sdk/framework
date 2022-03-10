<?php

namespace Netflex\Http;

use Netflex\Http\Contracts\HttpClient;
use Psr\Http\Message\ResponseInterface;

use Netflex\Http\Concerns\ParsesResponse;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Exception\GuzzleException as Exception;

class Client implements HttpClient
{
  use ParsesResponse;

  /** @var GuzzleClient */
  protected $client;

  /**
   * @param array $options
   */
  public function __construct(array $options = [])
  {
    $this->client = new GuzzleClient($options);
  }

  protected function buildPayload($payload)
  {
    return ['json' => $payload];
  }

  /**
   * @param string $url
   * @return ResponseInterface
   */
  public function getRaw($url)
  {
    return $this->client->get($url);
  }

  /**
   * @param string $url
   * @return PromiseInterface
   */
  public function getRawAsync($url)
  {
    return $this->client->getAsync($url);
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function get($url, $assoc = false)
  {
    return $this->parseResponse($this->getRaw($url), $assoc);
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function getAsync($url, $assoc = false)
  {
    return $this->getRawAsync($url)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return ResponseInterface
   */
  public function putRaw($url, $payload)
  {
    return $this->client->put($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return PromiseInterface
   */
  public function putRawAsync($url, $payload)
  {
    return $this->client->putAsync($url);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function put($url, $payload = [], $assoc = false)
  {
    return $this->parseResponse($this->putRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function putAsync($url, $payload = [], $assoc = false)
  {
    return $this->putRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return ResponseInterface
   */
  public function postRaw($url, $payload)
  {
    return $this->client->post($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return PromiseInterface
   */
  public function postRawAsync($url, $payload)
  {
    return $this->client->postAsync($url);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function post($url, $payload = [], $assoc = false)
  {
    return $this->parseResponse($this->postRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function postAsync($url, $payload = [], $assoc = false)
  {
    return $this->postRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @return ResponseInterface
   */
  public function deleteRaw($url)
  {
    return $this->client->delete($url);
  }

  /**
   * @param string $url
   * @return PromiseInterface
   */
  public function deleteRawAsync($url)
  {
    return $this->client->deleteAsync($url);
  }

  /**
   * @param string $url
   * @return mixed
   * @throws Exception
   */
  public function delete($url, $assoc = false)
  {
    return $this->parseResponse($this->deleteRaw($url), $assoc);
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function deleteAsync($url, $assoc = false)
  {
    return $this->deleteRawAsync($url)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }
}
