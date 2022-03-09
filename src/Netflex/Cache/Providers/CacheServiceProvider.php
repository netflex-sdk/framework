<?php

namespace Netflex\Cache\Providers;

use Carbon\Carbon;
use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class CacheServiceProvider extends ServiceProvider
{
    public function register()
    {
        Repository::macro('rememberUntilTomorrow', function (string $key, $callback) {
            /** @var Repository $this */
            return $this->remember($key, seconds_until_end_of_today(), $callback);
        });
    }
}
