<?php

namespace Netflex\Foundation\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{    
    public function boot()
    {
        Event::listen('bootstrapped: Illuminate\Foundation\Bootstrap\BootProviders', function () {
            foreach (Arr::dot(Config::all()) as $key => $value) {
                if (is_string($value)) {
                    if (preg_match('/^#(\w+)#$/', $value, $matches)) {
                        Config::set($key, variable($matches[1]));
                    }
                }
            }
        });
    }
}