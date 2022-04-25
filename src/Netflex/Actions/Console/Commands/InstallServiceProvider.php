<?php

namespace Netflex\Actions\Console\Commands;

use Illuminate\Console\Command;

class InstallServiceProvider extends Command
{
    protected $signature = 'actions:install:service-provider';

    protected $description = 'Creates the ActionServiceProvider if it doesn\'t already exist';

    public function handle()
    {
        $this->createServiceProviderIfNotExists();

    }

    private function createServiceProviderIfNotExists()
    {
        $path = app_path('Providers/ActionServiceProvider.php');

        if (!file_exists($path)) {
            $this->warn("Creating [ActionServiceProvider] service provider");
            file_put_contents($path, file_get_contents(__DIR__ . "/stubs/service-provider.stub"));
        } else {
            $this->warn("Service provider [ActionServiceProvider] already exists");
        }

    }

}
