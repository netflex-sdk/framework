<?php

namespace Netflex\Foundation;

use Netflex\API\Facades\API;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Label
{
  public static function locales(): array
  {
    return array_keys(static::all());
  }

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

    foreach (Config::get('labels.aliases', []) as $alias => $locale) {
      if (array_key_exists($locale, $locales)) {
        $aliased = $locales[$alias] ?? [];
        foreach ($locales[$locale] as $key => $label) {
          if (!isset($aliased[$key])) {
            $aliased[$key] = $label;
          }
        }
        $locales[$alias] = $aliased;
      }
    }

    return $locales;
  }

  public static function only(...$locales)
  {
    $labels = static::all();
    $only = [];

    foreach ($locales as $locale) {
      $only[$locale] = [];
      if (array_key_exists($locale, $labels)) {
        $only[$locale] = $labels[$locale];
      }
    }

    foreach ($only as &$value) {
      if (empty($value)) {
        $value = (object) [];
      }
    }

    return $only;
  }
}
