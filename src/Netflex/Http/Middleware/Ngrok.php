<?php

namespace Netflex\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

class Ngrok
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    if ($request->server->has('HTTP_X_ORIGINAL_HOST')) {
      URL::forceRootUrl(($request->server->get('HTTP_X_FORWARDED_PROTO') ?? 'http') . '://' . $request->server->get('HTTP_X_ORIGINAL_HOST'));
    }

    return $next($request);
  }
}
