<?php

namespace Netflex\API\Events;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestSucceeded
{
  public RequestInterface $request;
  public ?ResponseInterface $response = null;

  public function __construct(RequestInterface $request, $response)
  {
    $this->request = $request;

    if ($response instanceof ResponseInterface)
      $this->response = $response;

    else if ($response instanceof FulfilledPromise)
      $this->response = $response->wait();
  }

}
