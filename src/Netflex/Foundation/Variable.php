<?php

namespace Netflex\Foundation;

use Netflex\API\Facades\API;

use Netflex\Support\Retrievable;
use Netflex\Support\ReactiveObject;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;

class Variable extends ReactiveObject
{
  use Retrievable;

  protected static $base_path = 'foundation/variables';

  /** @var array */
  protected $timestamps = [];

  /**
   * Retrieve the value of a Setting
   *
   * @param string $key
   * @param mixed $fallback = null
   * @return mixed
   */
  public static function get($key, $fallback = null)
  {
    $setting = static::retrieve($key);
    return $setting ? $setting->value : $fallback;
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function getValueAttribute($value)
  {
    switch ($this->format) {
      case 'boolean':
        return (bool) (int) $value;
      case 'json':
        if (is_string($value)) {
          return json_decode($value);
        }

        return $value;
      default:
        return $value;
    }
  }

  /**
   * @return Collection|static[]
   */
  public static function all()
  {
    $templates = Cache::rememberForever('variables', function () {
      return API::get('foundation/variables');
    });

    return collect($templates)->map(function ($content) {
      return new static($content);
    });
  }

  /**
   * @param string $alias
   * @return static|void
   */
  public static function retrieve($alias)
  {
    if (Facade::getFacadeApplication() && App::isBooted()) {
      return static::all()->first(function ($content) use ($alias) {
        return $content->alias === $alias;
      });
    }

    return new static(['alias' => $alias, 'value' => '#' . $alias . '#']);
  }
}
