<?php

namespace Netflex\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class ClearCache
{
  protected $parameter = '_clearcache';

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle($request, Closure $next)
  {
    if ($request->exists($this->parameter)) {
      Log::debug('Middleware => ClearCache');
      $request->request->remove($this->parameter);
      Artisan::call('cache:clear');
      usleep(random_int(250000, 1500000));
    }

    return $next($request);
  }
}
