<?php

namespace Netflex\Http\Events;

use Psr\Http\Message\RequestInterface;

class RequestSending
{
  public RequestInterface $request;

  public function __construct(RequestInterface $request)
  {
    $this->request = $request;
  }
}
