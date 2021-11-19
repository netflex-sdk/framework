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
class Redirect extends ReactiveObject
{
  /**
   * @return static[]
   */
  public static function all()
  {
    $redirects = Cache::rememberForever('redirects', function () {
      return API::get('foundation/redirects');
    });

    return collect($redirects)->map(function ($redirect) {
      return new static($redirect);
    });
  }

  /**
   * @param string $id
   * @return static|void
   */
  public static function retrieve($id)
  {
    return static::all()->first(function ($redirect) use ($id) {
      return $redirect->id === $id;
    });
  }
}
