<?php

namespace Netflex\Render\Http\Middleware;

use Closure;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Netflex\Render\Exceptions\RenderException;
use Netflex\Render\HTML;

class SSR
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $option
     * @param mixed $parameters
     * @return mixed
     *
     * @throws RenderException
     */
    public function handle($request, Closure $next, $option = null, ...$parameters)
    {
        $render = function () use ($request, $next) {
            /** @var Response */
            $response = $next($request);

            if ($content = $response->getContent()) {
                $request = new Request();

                $request->headers->replace(
                    $response->headers->all()
                );

                return HTML::from($content)->toResponse($request);
            }

            return $response->header('X-SSR', 0);
        };

        if ($option === 'cache') {
            $key = request_ssr_key($request);
            $ttl = (int) array_shift($parameters);

            $response = (int) $ttl > 0
                ? Cache::remember($key, $ttl, $render)
                : Cache::rememberForever($key, $render);

            if ($response) {
                return $response->header('X-SSR-Cache-Hit', $key, true);
            }
        }

        return $render();
    }
}
