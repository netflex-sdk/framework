<?php

namespace Netflex\Console;

use Netflex\Console\ArtisanServiceProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Laravel\Tinker\TinkerServiceProvider;
use NunoMaduro\LaravelConsoleMenu\LaravelConsoleMenuServiceProvider;

use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider as ServiceProvider;

class ConsoleSupportServiceProvider extends ServiceProvider
{
  /**
   * The provider class names.
   *
   * @var array
   */
  protected $providers = [
    ArtisanServiceProvider::class,
    ComposerServiceProvider::class,
    TinkerServiceProvider::class,
    LaravelConsoleMenuServiceProvider::class
  ];
}
