<?php

namespace Netflex\API\Contracts;

use Netflex\Http\Contracts\HttpClient;

interface APIClient extends HttpClient
{
  /**
   * @return string|null
   */
  public function getConnectionName ();

  /**
   * @param string|null $name
   * @return static
   */
  public function setConnectionName ($name);
}
