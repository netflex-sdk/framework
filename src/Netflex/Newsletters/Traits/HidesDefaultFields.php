<?php

namespace Netflex\Newsletters\Traits;

trait HidesDefaultFields
{
  public static function bootHidesDefaultFields()
  {
    $defaults = [
      'to_qty',
      'to_group',
      'to_segment',
      'to_list',
    ];

    static::retrieved(function ($model) use ($defaults) {
      $model->hidden = array_merge($model->hidden, $defaults);
    });

    static::created(function ($model) use ($defaults) {
      $model->hidden = array_merge($model->hidden, $defaults);
    });
  }
}
