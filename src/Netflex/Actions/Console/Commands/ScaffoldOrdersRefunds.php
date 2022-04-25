<?php

namespace Netflex\Actions\Console\Commands;

use Illuminate\Console\Command;

class ScaffoldOrdersRefunds extends Command
{
    private $lines = [];
    protected $signature = 'actions:scaffold:orders:refunds';

    protected $description = 'Scaffolds code stubs for the Orders\Refunds functionality';

    public function handle()
    {
        $this->createServiceProviderIfNotExists();
        $dir = app_path("Actions/Orders/Refunds/");

        if(!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if(!file_exists($dir . "/FormController.php") || $this->confirm("FormController already exists. Do you want to overwrite it?")) {
            $this->info("Writing [App\\Actions\\Orders\\Refunds\\FormController]");
            file_put_contents($dir . "/FormController.php", file_get_contents(__DIR__ . "/stubs/Orders/Refunds/describer.stub"));
        } else {
            $this->warn("[App\\Actions\\Orders\\Refunds\\FormController] already exists and has been skipped");
        }

        if(!file_exists($dir . "/ActionController.php") || $this->confirm("ActionController already exists. Do you want to overwrite it?")) {
            $this->info("Writing [App\\Actions\\Orders\\Refunds\\ActionController]");
            file_put_contents($dir . "/ActionController.php", file_get_contents(__DIR__ . "/stubs/Orders/Refunds/action-controller.stub"));
        }

        $this->updateUsesWithCode('use App\\Actions\\Orders\\Refunds\\ActionController as OrdersRefundsActionController;');
        $this->updateUsesWithCode('use App\\Actions\\Orders\\Refunds\\FormController as OrdersRefundsDescriber;');
        $this->updateServiceProviderWithCode('      $this->setOrdersRefundsActionControllers(OrdersRefundsDescriber::class, OrdersRefundsActionController::class);');

        $this->warn("Remember to add the service provider into your [config/app.php] file");
    }

    private function createServiceProviderIfNotExists() {
        $this->runCommand('actions:install:service-provider', [], $this->output);
    }

    private function updateUsesWithCode(string $code, ?string $file = null) {

        $file = $file ?? app_path("Providers/ActionServiceProvider.php");
        file_put_contents($file, preg_replace("/(namespace.*;)/D", "$1\r\n$code\r\n", file_get_contents($file)));
    }
    private function updateServiceProviderWithCode(string $code, ?string $file = null) {
        $file = $file ?? app_path("Providers/ActionServiceProvider.php");
        file_put_contents($file, preg_replace("/(function +registerProviders.*(\W*).*\r?\n?\W*\{)/D", "$1\r\n$code\r\n", file_get_contents($file)));
    }

}
