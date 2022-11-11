<?php

namespace Netflex\Commerce\Providers;

use Illuminate\Support\ServiceProvider;
use Netflex\Commerce\Order;

class CommerceServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->bound('events')) {
            Order::setEventDispatcher($this->app['events']);
        }
    }
}
