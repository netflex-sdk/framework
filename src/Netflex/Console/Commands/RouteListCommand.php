<?php

namespace Netflex\Console\Commands;

use Illuminate\Routing\Route;
use Illuminate\Routing\RedirectController;
use Illuminate\Foundation\Console\RouteListCommand as Command;
use Netflex\Foundation\Redirect;

class RouteListCommand extends Command {
    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
      $information = parent::getRouteInformation($route);

      if ($information['action'] === RedirectController::class) {
        $information['action'] = 'Redirect';
        $information['method'] = Redirect::retrieve($information['name'])->type ?? '302';
      }

      $information['action'] = basename(str_replace('\\', '/', $information['action']));

      return $information;
    }
}
