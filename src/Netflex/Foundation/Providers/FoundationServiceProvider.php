<?php

namespace Netflex\Foundation\Providers;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register()
    {
        app('events')->listen("bootstrapping: Illuminate\Foundation\Bootstrap\BootProviders", function() {
            $old = app('config')->all();
            $this->app->instance('config', new Repository($this->replace_variables($old)));
        });
    }

    private function replace_variables($config, $i = 0) {
        if(is_array($config)) {
            return collect($config)
                ->map(fn($value) => $this->replace_variables($value, $i + 1))
                ->toArray();
        } if(is_string($config) && strlen($config) >= 3 && $config[0] === "#" && $config[strlen($config) - 1] === "#") {
            return variable(substr($config, 1, -1));
        } else {
            return $config;
        }
    }
}
