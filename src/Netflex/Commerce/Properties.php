<?php

namespace Netflex\Commerce;

use Exception;
use Netflex\Structure\Traits\Localizable;
use Netflex\Support\ReactiveObject;

class Properties extends ReactiveObject
{
  protected $readOnlyAttributes = [];

  use Localizable;

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    $attributes = empty($this->attributes) ? [] : $this->attributes;

    foreach ($attributes as $key => $value) {
      if (!is_scalar($value)) {
        unset($attributes[$key]);
      }
    }

    return $attributes;
  }
}
