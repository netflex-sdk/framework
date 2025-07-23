<?php

namespace Netflex\Http;

use GuzzleHttp\BodySummarizer;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Utils;
use Netflex\Http\Contracts\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Netflex\Http\Concerns\ParsesResponse;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException as Exception;
use Psr\Http\Message\UriInterface;

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
    // Increases the guzzle error message limit before truncation.
    // Ref: https://github.com/guzzle/guzzle/issues/2185#issuecomment-800293420,
    // https://stackoverflow.com/a/78401816
    $stack = new HandlerStack(Utils::chooseHandler());
    $stack->push(
      Middleware::httpErrors(new BodySummarizer(1000000)),
      'http_errors',
    );
    $stack->push(Middleware::redirect(), 'allow_redirects');
    $stack->push(Middleware::cookies(), 'cookies');
    $stack->push(Middleware::prepareBody(), 'prepare_body');

    $this->client = new GuzzleClient(array_merge(
      ['handler' => $stack],
      $options,
    ));
  }

  protected function buildPayload($payload)
  {
    if ($payload !== null) {
      return ['json' => $payload];
    }

    return [];
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
    return $this->client->postAsync($url, $this->buildPayload($payload));
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
   * @param array|null $payload = null
   * @return ResponseInterface
   */
  public function deleteRaw($url, $payload = null)
  {
    return $this->client->delete($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @return PromiseInterface
   */
  public function deleteRawAsync($url, $payload = null)
  {
    return $this->client->deleteAsync($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @return mixed
   * @throws Exception
   */
  public function delete($url, $payload = null, $assoc = false)
  {
    return $this->parseResponse($this->deleteRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function deleteAsync($url, $payload = null, $assoc = false)
  {
    return $this->deleteRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   *
   * @throws GuzzleException
   */
  public function sendRaw(
    RequestInterface $request,
    array $options = [],
  ) {
    return $this->client->send($request, $options);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   *
   * @throws GuzzleException
   */
  public function sendAsyncRaw(
    RequestInterface $request,
    array $options = [],
  ) {
    return $this->client->sendAsync($request, $options);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   * @param bool $assoc
   *
   * @throws GuzzleException
   */
  public function send(
    RequestInterface $request,
    array $options = [],
    $assoc = false,
  ) {
    return $this->parseResponse($this->sendRaw($request, $options), $assoc);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   * @param bool $assoc
   *
   * @throws GuzzleException
   */
  public function sendAsync(
    RequestInterface $request,
    array $options = [],
    $assoc = false,
  ) {
    return $this->sendAsyncRaw($request, $options)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $method
   * @param UriInterface|string $uri
   * @param array $options
   * @param bool $assoc
   * @return mixed
   * @throws Exception
   */
  public function request(
    string $method,
    UriInterface|string $uri = '',
    array $options = [],
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse(
      $this->client->request($method, $uri, $options),
      $assoc,
    );
  }

  /**
   * @param string $method
   * @param UriInterface|string $uri
   * @param array $options
   * @param bool $assoc
   * @return PromiseInterface
   */
  public function requestAsync(
    string $method,
    UriInterface|string $uri = '',
    array $options = [],
    bool $assoc = false,
  ): PromiseInterface {
    return $this->client->requestAsync($method, $uri, $options)
      ->then(fn ($response) => ($this->parseResponse($response, $assoc)));
  }
}
