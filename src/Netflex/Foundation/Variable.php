<?php

namespace Netflex\Foundation;

use Illuminate\Support\Collection;
use Netflex\Support\ReactiveObject;
use Netflex\Support\Retrievable;

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
        return (bool)(int)$value;
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
    return once(function () {
      $templates = app('cache')->rememberForever('variables', function () {
        return app('api.client')->get('foundation/variables');
      });

      return collect($templates)->map(function ($content) {
        return new static($content);
      });
    });
  }



  static $data = null;
  /**
   * @param string $alias
   * @return static|void
   */
  public static function retrieve($alias)
  {
    static $loaded = false;
    $services_uncached = $loaded || app()->has('api.client') && app()->has('cache');

    if ($services_uncached) {
      $loaded = true;
      static::$data ??= static::all()->keyBy('alias');
      return static::$data[$alias] ?? null;
    }

    return new static(['alias' => $alias, 'value' => '#' . $alias . '#']);
  }
}
