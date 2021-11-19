<?php

namespace Netflex\Foundation\Providers;

use Netflex\Foundation\LabelLoader;
use Netflex\Foundation\LabelTranslator;

use Illuminate\Translation\TranslationServiceProvider as ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {
    $this->registerLoader();

    $this->app->singleton('translator', function ($app) {
      $loader = $app['translation.loader'];

      // When registering the translator component, we'll need to set the default
      // locale as well as the fallback locale. So, we'll grab the application
      // configuration so we can easily get both of these values from there.
      $locale = $app['config']['app.locale'];

      $trans = new LabelTranslator($loader, $locale);

      $trans->setFallback($app['config']['app.fallback_locale']);

      return $trans;
    });
  }

  /**
   * Register the translation line loader.
   *
   * @return void
   */
  protected function registerLoader()
  {
    $this->app->singleton('translation.loader', function ($app) {
      return new LabelLoader($app['files'], $app['path.lang']);
    });
  }
}
