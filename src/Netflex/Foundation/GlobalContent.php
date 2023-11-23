<?php

namespace Netflex\Foundation;

use Netflex\API\Facades\API;
use Netflex\Support\ReactiveObject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property int $template_id
 * @property string $name
 * @property string $alias
 * @property string $description
 * @property string $area_type
 * @property string $content_type
 * @property bool $has_subpages
 * @property string $code
 * @property bool $active
 * @property Collection $globals
 */
class GlobalContent extends ReactiveObject
{
  /**
   * @param array $globals
   * @return Collection
   */
  public function getGlobalsAttribute($globals = []): Collection
  {
    return collect($globals);
  }

  /**
   * @return static[]
   */
  public static function all()
  {
    return once(function () {
      $content = Cache::rememberForever('statics', function () {
        return API::get('foundation/globals');
      });

      return collect($content)->map(function ($content) {
        return new static($content);
      });
    });
  }

  /**
   * @param string $alias
   * @return static|void
   */
  public static function retrieve($alias)
  {
    return static::all()->first(function ($content) use ($alias) {
      return $content->alias === $alias;
    });
  }
}
