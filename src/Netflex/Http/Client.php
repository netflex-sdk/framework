<?php

namespace Netflex\Http;

use GuzzleHttp\BodySummarizer;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Utils;
use Netflex\Http\Concerns\ParsesResponse;
use Netflex\Http\Contracts\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class Client implements HttpClient
{
  use ParsesResponse;

  protected \GuzzleHttp\Client $client;

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

    $this->client = new \GuzzleHttp\Client(
      array_merge(
        ['handler' => $stack],
        $options,
      ),
    );
  }

  protected function buildPayload($payload): array
  {
    if ($payload !== null) {
      return ['json' => $payload];
    }

    return [];
  }

  /**
   * @param string $url
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function getRaw(string $url): ResponseInterface
  {
    return $this->client->get($url);
  }

  /**
   * @param string $url
   * @return PromiseInterface
   */
  public function getRawAsync(string $url): PromiseInterface
  {
    return $this->client->getAsync($url);
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function get(string $url, bool $assoc = false): mixed
  {
    return $this->parseResponse($this->getRaw($url), $assoc);
  }

  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function getAsync(string $url, bool $assoc = false): PromiseInterface
  {
    return $this->getRawAsync($url)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function putRaw(string $url, array|null $payload): ResponseInterface
  {
    return $this->client->put($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return PromiseInterface
   */
  public function putRawAsync(
    string $url,
    array|null $payload,
  ): PromiseInterface {
    return $this->client->putAsync($url, $payload);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function put(
    string $url,
    array|null $payload = [],
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse($this->putRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function putAsync(
    string $url,
    array|null $payload = [],
    bool $assoc = false,
  ): PromiseInterface {
    return $this->putRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function postRaw(string $url, array|null $payload): ResponseInterface
  {
    return $this->client->post($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @return PromiseInterface
   */
  public function postRawAsync(
    string $url,
    array|null $payload,
  ): PromiseInterface {
    return $this->client->postAsync($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function post(
    string $url,
    array|null $payload = [],
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse($this->postRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function postAsync(
    string $url,
    array|null $payload = [],
    bool $assoc = false,
  ): PromiseInterface {
    return $this->postRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function deleteRaw(
    string $url,
    array|null $payload = null,
  ): ResponseInterface {
    return $this->client->delete($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @return PromiseInterface
   */
  public function deleteRawAsync(
    string $url,
    array|null $payload = null,
  ): PromiseInterface {
    return $this->client->deleteAsync($url, $this->buildPayload($payload));
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @param bool $assoc
   * @return mixed
   * @throws GuzzleException
   */
  public function delete(
    string $url,
    array|null $payload = null,
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse($this->deleteRaw($url, $payload), $assoc);
  }

  /**
   * @param string $url
   * @param array|null $payload = null
   * @param boolean $assoc = false
   * @return PromiseInterface
   */
  public function deleteAsync(
    string $url,
    array|null $payload = null,
    bool $assoc = false,
  ): PromiseInterface {
    return $this->deleteRawAsync($url, $payload)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   *
   * @return ResponseInterface
   * @throws GuzzleException
   */
  public function sendRaw(
    RequestInterface $request,
    array $options = [],
  ): ResponseInterface {
    return $this->client->send($request, $options);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   * @return PromiseInterface
   */
  public function sendAsyncRaw(
    RequestInterface $request,
    array $options = [],
  ): PromiseInterface {
    return $this->client->sendAsync($request, $options);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   * @param bool $assoc
   *
   * @return mixed
   * @throws GuzzleException
   */
  public function send(
    RequestInterface $request,
    array $options = [],
    bool $assoc = false,
  ): mixed {
    return $this->parseResponse($this->sendRaw($request, $options), $assoc);
  }

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given
   *                       request and to the transfer. See \GuzzleHttp\RequestOptions.
   * @param bool $assoc
   *
   * @return PromiseInterface
   */
  public function sendAsync(
    RequestInterface $request,
    array $options = [],
    bool $assoc = false,
  ): PromiseInterface {
    return $this->sendAsyncRaw($request, $options)
      ->then(fn ($response) => $this->parseResponse($response, $assoc));
  }

  /**
   * @param string $method
   * @param UriInterface|string $uri
   * @param array $options
   * @param bool $assoc
   * @return mixed
   * @throws GuzzleException
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
