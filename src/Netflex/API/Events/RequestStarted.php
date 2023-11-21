<?php

namespace Netflex\API\Events;

use Psr\Http\Message\RequestInterface;

class RequestStarted
{
  public RequestInterface $request;

  public function __construct(RequestInterface $request) {
    $this->request = $request;
  }
}
