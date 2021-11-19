<?php

namespace Netflex\Foundation;

use Netflex\API\Facades\API;
use Netflex\Support\ReactiveObject;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Support\Responsable;

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
  public function getAliasAttribute ($alias) {
    if ($alias) {
      return str_replace('/', '.', $alias);
    }
  }

  /**
   * @param string|null $alias 
   */
  public function setAliasAttribute ($alias) {
    if ($alias) {
      $alias = str_replace('.', '/', $alias);
    }

    return $this->attributes['alias'] = $alias;
  }

  /**
   * Create an HTTP response that represents the object.
   *
   * @param array $variables
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function toResponse($variables = [])
  {
    return View::make("templates/{$this->alias}", $variables)
      ->render();
  }

  /**
   * @return static[]
   */
  public static function all()
  {
    $templates = Cache::rememberForever('templates', function () {
      return API::get('foundation/templates');
    });

    return collect($templates)->map(function ($template) {
      return new static($template);
    });
  }

    /**
   * @param int $id
   * @return static|void
   */
  public static function retrieve($id)
  {
    return static::all()->first(function ($template) use ($id) {
      return $template->id === $id;
    });
  }
}
