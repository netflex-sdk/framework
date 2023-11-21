<?php

namespace Netflex\API\Events;


use GuzzleHttp\Exception\RequestException;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestFailed
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public RequestInterface $request;
  public ?ResponseInterface $response = null;
  public \Throwable $exception;

  public function __construct(RequestInterface $request, \Throwable $exception)
  {
    $this->request = $request;
    if ($exception instanceof RequestException) {
      $this->response = $exception->getResponse();
    }
    $this->exception = $exception;
  }
}
