<?php

namespace Netflex\Foundation;

use Netflex\API\Facades\API;
use Netflex\Support\ReactiveObject;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Support\Responsable;

class Label extends ReactiveObject
{
  /**
   * @return static[]
   */
  public static function all()
  {
    $locales = Cache::rememberForever('labels', function () {
      return API::get('foundation/labels/i18n', true);
    });

    foreach ($locales as $locale => $labels) {
      $locales[$locale] = collect($labels)->map(function ($label) {
        return $label;
      });
    }

    return $locales;
  }
}
