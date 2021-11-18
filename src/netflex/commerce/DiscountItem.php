<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\DiscountItemAPI;

/**
 * @property int $id
 * @property int $order_id
 * @property int $discount_id
 * @property string $scope
 * @property string $scope_key
 * @property string $label
 * @property string $type
 * @property float $discount
 */
class DiscountItem extends ReactiveObject
{
  use DiscountItemAPI;

  protected $readOnlyAttributes = [
    'id',
    'order_id'
  ];

  public function getOrderIdAttribute($value)
  {
    return (int) $value;
  }

  public function getDiscountIdAttribute($value)
  {
    return (int) $value;
  }

  public function getDiscountAttribute($value)
  {
    return (float) $value;
  }
}
