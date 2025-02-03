<?php

namespace Netflex\Commerce;

use Illuminate\Contracts\Support\Arrayable;
use Netflex\Structure\Traits\Localizable;
use Netflex\Support\ReactiveObject;

class Properties extends ReactiveObject implements Arrayable
{
  protected $readOnlyAttributes = [];

  use Localizable;

  /**
   * @return array
   */
  #[\ReturnTypeWillChange]
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

  public function toArray(): array
  {
    return $this->attributes;
  }

  public function toModifiedArray(): array
  {
    if (!empty($this->modified)) {
      return json_decode(json_encode($this->attributes), true);
    }

    return [];
  }
}
