<?php

namespace Netflex\Newsletters\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;

use Illuminate\Support\ServiceProvider;
use Netflex\Pages\Controllers\Controller;

class NewslettersServiceProvider extends ServiceProvider
{
  /**
   * @return void
   */
  public function register()
  {
    $this->registerComponents();
    $this->registerDirectives();
  }

  public function boot()
  {

    $this->publishes([
      __DIR__ . '/../config/newsletters.php' => $this->app->configPath('newsletters.php')
    ], 'config');

    $this->mergeConfigFrom(
      __DIR__ . '/../config/newsletters.php',
      'pages'
    );

    if ($this->app->bound('events')) {
      Controller::setEventDispatcher($this->app['events']);
    }
  }

  protected function registerComponents()
  {
    $prefix = Config::get('newsletters.prefix', 'newsletters');

    $components = Config::get('newsletters.components', []);

    if ($prefix) {
      $this->loadViewComponentsAs($prefix, $components);
    } else {
      foreach ($components as $alias => $component) {
        Blade::component($component, (is_string($alias) ? $alias : null));
      }
    }
  }

  protected function registerDirectives()
  {
    
  }
}
