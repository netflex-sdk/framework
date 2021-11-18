<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;

/**
 * @property-read int $id
 * @property-read int $order_id
 * @property-read int $discount_id
 * @property-read string $scope
 * @property-read string $scope_key
 * @property-read string $label
 * @property-read string $type
 * @property-read float $discount
 */
class DiscountData extends ReactiveObject
{
  protected $readOnlyAttributes = [
    'id',
    'order_id',
    'discount_id',
    'scope',
    'scope_key',
    'label',
    'type',
    'discount'
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
