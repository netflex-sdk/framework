<?php

namespace Netflex\Http\Contracts;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;

interface HttpClient
{
  /**
   * @param string $url
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function get($url, $assoc = false);

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function put($url, $payload = [], $assoc = false);

  /**
   * @param string $url
   * @param array|null $payload = []
   * @param boolean $assoc = false
   * @return mixed
   * @throws Exception
   */
  public function post($url, $payload = [], $assoc = false);

  /**
   * @param string $url
   * @param array|null $payload = null
   * @return mixed
   * @throws Exception
   */
  public function delete($url, $payload = null, $assoc = false);

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
