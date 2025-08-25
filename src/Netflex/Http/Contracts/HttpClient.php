<?php

namespace Netflex\Http\Contracts;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;

interface HttpClient
{
  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function get(string $url, bool $assoc = false): mixed;

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function put(
    string $url,
    array|null $payload,
    bool $assoc = false,
  ): mixed;

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws GuzzleException
   */
  public function post(
    string $url,
    array|null $payload,
    bool $assoc = false,
  ): mixed;

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
  ): mixed;

  /**
   * @param RequestInterface $request
   * @param array $options Request options to apply to the given request and to
   *                       the transfer. See \GuzzleHttp\RequestOptions.
   * @param bool $assoc
   *
   * @throws GuzzleException
   */
  public function send(
    RequestInterface $request,
    array $options = [],
    bool $assoc = false,
  );
}
