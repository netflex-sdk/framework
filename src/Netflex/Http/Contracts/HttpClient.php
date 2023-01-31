<?php

namespace Netflex\Http\Contracts;

use Exception;

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
}
