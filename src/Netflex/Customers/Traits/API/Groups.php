<?php

namespace Netflex\Customers\Traits\API;

use GuzzleHttp\Exception\GuzzleException;
use Netflex\API\Facades\API;

trait Groups
{
  /**
   * @throws GuzzleException
   */
  public function save(): void
  {
    $payload = [];

    if (!$this->id) {
      $this->attributes['id'] = API::post(trim(static::$base_path, '/'), $payload)
        ->group_id;
    } else {
      if (count($this->modified)) {
        API::put(trim(static::$base_path, '/') . '/' . $this->id, $payload);
      }
    }

    $this->refresh();
  }

  /**
   * @throws GuzzleException
   */
  public function refresh(): static
  {
    $this->attributes = API::get(trim(static::$base_path, '/') . '/' . $this->id, true);

    return $this;
  }

  /**
   * Creates empty order object based on orderData
   *
   * @param array $group
   * @return static
   * @throws GuzzleException
   */
  public static function create(array $group = []): static
  {
    return static::retrieve(
      API::post(trim(static::$base_path, '/'), $group)
        ->group_id
    );
  }
}
