<?php

namespace Netflex\API\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Psr\Http\Message\RequestInterface;

class RequestStarted
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public RequestInterface $request;

  public function __construct(RequestInterface $request) {
    $this->request = $request;
  }
}
