<?php

namespace Netflex\Cache\Providers;

use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider;

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
