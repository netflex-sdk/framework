<?php

namespace Netflex\Foundation\Providers;

use Illuminate\Config\Repository as Cache;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register()
    {
        app('events')->listen("bootstrapping: Illuminate\Foundation\Bootstrap\BootProviders", function () {
            /** @var Cache $instance */
            $instance = $this->app->make('config');
            $this->replace_variables($instance, $instance->all());
        });
    }

    private function replace_variables(Cache $instance, $config, $keys = [])
    {
        if (is_array($config)) {
            return collect($config)
                ->map(fn ($value, $key) => $this->replace_variables($instance, $value, array_merge($keys, [$key])))
                ->toArray();
        }
        if (is_string($config) && strlen($config) >= 3 && $config[0] === "#" && $config[strlen($config) - 1] === "#") {
            $instance->set(implode('.', $keys), variable(substr($config, 1, -1)));
        }
    }
}
