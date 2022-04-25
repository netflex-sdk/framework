<?php

namespace Netflex\Actions\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

class InstallCommand extends Command
{
    private $lines = [];
    protected $signature = 'actions:install';

    protected $description = 'Installs the actions package';

    public function handle() {

        $this->runCommand('cache:clear', [], $this->output);

        $this->addOrChangeVariable();
        $this->createServiceProviderIfNotExists();

        $this->runAppends();
        $this->info("Done");
    }


    private function addOrChangeVariable() {
        $variable = Variable::retrieve('netflex_actions', null);
        if($variable == null || $this->confirm("The [netflex_actions] variable already exists, do you want to overwrite it with default settings?")) {
            $this->appendWarn("Remember to update the base url and enable the relevant functionality in the [netflex_actions] setting");

            $value = [
                'url' => [
                    'basePath' => "https://site-alias.netflex.dev",
                    'overrides' => new \stdClass(),
                ],
                'functionality' => [
                    'orders' => [
                        'refunds' => [
                            'enabled' => false
                        ]
                    ],
                    'reservations' => [
                        'availability' => [
                            'enabled' => false
                        ]
                    ]
                ]
            ];

            if(!$variable) {
                API::post('foundation/variables', [
                    'name' => 'Netflex Actions',
                    'alias' => 'netflex_actions',
                    'format' => 'json',
                    'value' => json_encode($value),
                ]);
            } else {
                API::put("foundation/variables/{$variable->id}", [
                    'value' => json_encode($value),
                ]);
            }
        }
    }

    private function createServiceProviderIfNotExists() {
        $this->runCommand('actions:install:service-provider', [], $this->output);
    }

    protected function runAppends() {
        foreach($this->lines as $append) {
            $append();
        }
    }
    protected function appendInfo(...$vars) {
        $this->lines[] = fn() => $this->info(...$vars);
    }

    protected function appendWarn(...$vars) {
        $this->lines[] = fn() => $this->warn(...$vars);
    }

}
