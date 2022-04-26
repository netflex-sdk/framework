<?php

namespace Netflex\Actions\Middlewares;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $nonce = $request->headers->get('X-Nonce');
        $digest = $request->headers->get('X-Digest');
        $timestamp = $request->headers->get('X-Timestamp');
        $datetime = Carbon::parse($timestamp);

        $digestOk = $digest && hash_hmac('SHA256', '$ts$nonce', variable('netflex_api') === $digest);
        $timeOk = $datetime && abs($datetime->diffInSeconds(Carbon::now())) < 30;
        $notRunned = Cache::get("run-$nonce", false) == false;

        $validated = ($digestOk && $timeOk && $notRunned) || !App::isProduction();

        if ($validated) {
            Cache::set("run-{$nonce}", true);
            Log::debug('Authorized webhook', ['ts' => $timestamp, 'nonce' => $nonce, 'digest' => $digest]);
            return $next($request);
        }

        Log::debug('Unauthorized webhook', ['ts' => $timestamp, 'nonce' => $nonce, 'digest' => $digest]);
        return abort(401);
    }
}
