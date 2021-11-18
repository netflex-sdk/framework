<?php

namespace Netflex\Commerce;

use Exception;
use Netflex\Support\ReactiveObject;

class Properties extends ReactiveObject
{
  protected $readOnlyAttributes = [];

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
