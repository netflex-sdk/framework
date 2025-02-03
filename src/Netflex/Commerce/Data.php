<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\DataAPI;

class Data extends ReactiveObject
{
  use DataAPI;

  public function toModifiedArray(): array
  {
    if (!empty($this->modified)) {
      return json_decode(json_encode($this->attributes), true);
    }

    return [];
  }
}
