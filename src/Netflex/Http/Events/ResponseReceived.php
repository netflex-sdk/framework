<?php

namespace Netflex\Http\Events;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseReceived
{
  public RequestInterface $request;
  public ResponseInterface $response;
  public float $requestDuration;

  public function __construct(RequestInterface $request, ResponseInterface $response, float $time)
  {
    $this->request = $request;
    $this->response = $response;
    $this->requestDuration = $time;
  }
}
