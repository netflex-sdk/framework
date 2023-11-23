<?php

namespace Netflex\API\Contracts;

use Netflex\Http\Contracts\HttpClient;

interface APIClient extends HttpClient
{
  /**
   * @return string|null
   */
  public function getName();

  /**
   * @param string|null $name
   * @return static
   */
  public function setName($name);
}
