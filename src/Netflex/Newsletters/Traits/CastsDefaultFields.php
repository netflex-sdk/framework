<?php

namespace Netflex\Newsletters\Traits;

use Netflex\Newsletters\Newsletter;

use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Netflex\API\Facades\API;
use Illuminate\Support\Str;

trait CastsDefaultFields
{
  public static function bootCastsDefaultFields()
  {
    $defaults = [
      'id' => 'int',
      'transactional' => 'bool',
      'automation' => 'bool',
      'userid' => 'int',
      'template_id' => 'int',
      'is_template' => 'bool',
      'original_id' => 'int'
    ];

    static::retrieved(function ($model) use ($defaults) {
      $model->casts = array_merge($model->casts, $defaults);
    });

    static::created(function ($model) use ($defaults) {
      $model->casts = array_merge($model->casts, $defaults);
    });
  }

  public function getOutputAttribute($output)
  {
    return new HtmlString($output);
  }

  /**
   * @return string
   */
  public function getTypeAttribute()
  {
    
    if($this->attributes['transactional'] && $this->attributes['automation']) {
      return Newsletter::TYPE_TRANSACTIONAL;
    }

    if($this->attributes['transactional']) {
      return Newsletter::TYPE_TRANSACTIONAL_CAMPAIGN;
    }
    
    if($this->attributes['automation']) {
      return Newsletter::TYPE_AUTOMATION;
    }

    if($this->attributes['is_template']) {
      return Newsletter::TYPE_TEMPLATE;
    }

    return Newsletter::TYPE_CAMPAIGN;
  }

  public function getToQueryAttribute($toQuery)
  {
    return json_decode($toQuery);
  }

}
