<?php

namespace Netflex\Http;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Facade;

use Illuminate\Routing\Pipeline;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

use Netflex\Http\Middleware\Ngrok;

class Kernel extends HttpKernel
{
  /**
   * @param  \Illuminate\Http\Request  $request
   * @param string $domain
   * @return \Illuminate\Http\Request
   */
  protected function injectDomain($request, $domain)
  {
    if ($domain) {
      $original = $request->headers->get('host');
      $request->headers->set('host', $domain);
      URL::forceRootUrl($request->getScheme() . '://' . $original);
      $this->app->bind('__current_domain__', function () use ($domain) {
        return $domain;
      });
    }

    return $request;
  }

  /**
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Request
   */
  protected function modifyRequest($request)
  {
    @list($host) = explode(':', $request->headers->get('host'));

    if ($this->app->environment() !== 'master') {
      if ($domains = $this->app['config']->get('domains')) {
        if (array_key_exists($host, $domains['mappings'] ?? [])) {
          return $this->injectDomain($request, $domains['mappings'][$host]);
        }

        if (isset($domains['default'])) {
          return $this->injectDomain($request, $domains['default']);
        }
      };
    }

    return $this->injectDomain($request, $host);
  }

  /**
   * Send the given request through the middleware / router.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  protected function sendRequestThroughRouter($request)
  {
    // Inject Request so we are able to bootstrap
    $this->app->instance('request', $request);
    $this->bootstrap();

    // Replace injected Request
    $this->app->instance('request', $this->modifyRequest($request));

    Facade::clearResolvedInstance('request');

    if (env('NGROK_PROXY')) {
      $this->pushMiddleware(Ngrok::class);
    }

    return (new Pipeline($this->app))
      ->send($request)
      ->through($this->app->shouldSkipMiddleware() ? [] : $this->middleware)
      ->then($this->dispatchToRouter());
  }
}
