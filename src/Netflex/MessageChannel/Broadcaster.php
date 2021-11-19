<?php

namespace Netflex\MessageChannel;

use Illuminate\Broadcasting\Broadcasters\Broadcaster as BaseBroadcaster;
use Illuminate\Contracts\Broadcasting\Broadcaster as BroadcasterContract;

use Netflex\MessageChannel\Facades\MessageChannel;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class Broadcaster extends BaseBroadcaster implements BroadcasterContract
{
  /**
   * Authenticate the incoming request for a given channel.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return mixed
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   */
  public function auth($request)
  {
    throw new AccessDeniedException();
  }

  /**
   * Return the valid authentication response.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  mixed  $result
   * @return mixed
   */
  public function validAuthenticationResponse($request, $result)
  {
    return $request;
  }

  /**
   * Broadcast the given event.
   *
   * @param  array  $channels
   * @param  string  $event
   * @param  array  $payload
   * @return void
   */
  public function broadcast(array $channels, $event, array $payload = [])
  {
    foreach ($channels as $channel) {
      MessageChannel::broadcast([
        'event' => $event,
        'payload' => $payload
      ], $channel);
    }
  }
}
