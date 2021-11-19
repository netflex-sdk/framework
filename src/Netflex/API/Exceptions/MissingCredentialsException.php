<?php

namespace Netflex\API\Exceptions;

use Exception;

class MissingCredentialsException extends Exception
{
  public function __construct()
  {
    parent::__construct('Missing Netflex API credentials, please verify your configuration');
  }
}
