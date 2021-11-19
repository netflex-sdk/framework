<?php

namespace Netflex\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

abstract class Kernel extends ConsoleKernel
{
  /**
   * Get the Artisan application instance.
   *
   * @return \Illuminate\Console\Application
   */
  protected function getArtisan()
  {
    return tap(parent::getArtisan(), function ($artian) {
      $artian->setName('Netflex');
    });
  }
}
