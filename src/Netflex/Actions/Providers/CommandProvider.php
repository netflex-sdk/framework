<?php

namespace Netflex\Actions\Providers;

use Illuminate\Support\ServiceProvider;
use Netflex\Actions\Console\Commands\InstallCommand;
use Netflex\Actions\Console\Commands\InstallServiceProvider;
use Netflex\Actions\Console\Commands\ScaffoldOrdersRefunds;

class CommandProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            InstallCommand::class,
            InstallServiceProvider::class,
            ScaffoldOrdersRefunds::class,
        ]);
    }
}
