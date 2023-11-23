<?php

namespace Netflex\Foundation;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Netflex\API\Facades\API;
use Netflex\Support\ReactiveObject;

class Template extends ReactiveObject implements Responsable
{
  /** @var array */
  protected $timestamps = [];

  /**
   * @return static|null
   */
  public static function get($id)
  {
    if (is_numeric($id)) {
      return static::retrieve($id);
    }
  }

  /**
   * @param array $areas
   * @return Collection
   */
  public function getAreasAttribute($areas = [])
  {
    return collect($areas);
  }

  /**
   * @param string|null $alias
   * @return string|null
   */
  public function getAliasAttribute($alias)
  {
    if ($alias) {
      return str_replace('/', '.', $alias);
    }
  }

  /**
   * @param string|null $alias
   */
  public function setAliasAttribute($alias)
  {
    if ($alias) {
      $alias = str_replace('.', '/', $alias);
    }

    return $this->attributes['alias'] = $alias;
  }

  public function getViewAttribute()
  {
    if ($this->type === 'newsletter') {
      return "newsletters.{$this->alias}";
    }

    return "templates.{$this->alias}";
  }

  /**
   * Create an HTTP response that represents the object.
   *
   * @param array $variables
   * @return \Symfony\Component\HttpFoundation\Response|string
   */
  public function toResponse($variables = [])
  {
    return View::make($this->view, $variables)->render();
  }


  static $templates = null;

  private static function getTemplates(): Collection
  {
    if(!static::$templates) {
      static::$templates = Cache::rememberForever('templates', function () {
        $data = API::get('foundation/templates');

        return collect($data)->map(function ($template) {
          return new static($template);
        })->keyBy('id');
      });
    }

    return static::$templates;
  }

  /**
   * @return static[]|Collection
   */
  public static function all()
  {
    return static::getTemplates()->values();
  }

  /**
   * @param int $id
   * @return static|void
   */
  public static function retrieve($id)
  {
    return static::getTemplates()[$id] ?? null;
  }
}
