<?php

namespace Netflex\Actions\Middlewares;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class WebhookAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $r
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $r, Closure $next)
    {
        $nonce = $r->headers->get("X-Nonce");
        $digest = $r->headers->get("X-Digest");
        $ts = $r->headers->get("X-Timestamp");
        $cTs = Carbon::parse($ts);

        if (($digest && hash_hmac("SHA256", "$ts$nonce", config("api.privateKey")) === $digest && $cTs && abs($cTs->diffInSeconds(Carbon::now())) < 30) || App::environment() === "local") {
            Cache::set("run-{$nonce}", true);
            Log::debug("Authorized webhook", ['ts' => $ts, 'nonce' => $nonce, 'digest' => $digest]);
            return $next($r);
        }

        Log::debug("Unauthorized webhook", ['ts' => $ts, 'nonce' => $nonce, 'digest' => $digest]);
        return abort(401);
    }
}
