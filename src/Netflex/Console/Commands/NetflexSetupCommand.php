<?php

namespace Netflex\Console\Commands;

use Exception;

class NetflexSetupCommand extends ServeCommand
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'netflex:setup';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Setups the Netflex integration';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  public function resolveSite()
  {
    $client = $this->client();
    $site = null;

    $this->progressBar = $this->output->createProgressBar(6);

    $client = $this->client();
    $site = null;

    try {
      throw new Exception('hei');
      $site = $client->get('auth/' . basename(app()->basePath()));
    } catch (Exception $e) {
      $sites = $client->get('auth');
      $menu = $this->menu('Please select what site this is')
        ->setForegroundColour('white')
        ->setBackgroundColour('red')
        ->setExitButtonText('Skip');

      collect($sites)->each(function ($site) use ($menu) {
        $menu->addOption($site->alias, $site->name . ' (' . $site->alias . ')');
      });

      if ($alias = $menu->open()) {
        try {
          $site = $client->get('auth/' . $alias);
        } catch (Exception $e) {
          $this->error('You dont have Content API access to this site');
          return;
        }
      }
    }

    return $site;
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $env = app()->basePath() . DIRECTORY_SEPARATOR . '.env';

    if (!file_exists($env)) {
      $env_example = app()->basePath() . DIRECTORY_SEPARATOR . '.env.example';

      if ($site = $this->resolveSite()) {
        $this->info('Creating .env file');
        copy($env_example, $env);
        $config = file_get_contents($env);
        $config .= "NETFLEX_PUBLIC_KEY={$site->public_key}\n";
        $config .= "NETFLEX_PRIVATE_KEY={$site->private_key}\n";
        file_put_contents($env, $config);
        $this->info('Netflex integration was setup');
        return;
      }

      $this->error('Unable to setup Netflex integration');
    }
  }
}
