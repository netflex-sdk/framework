<?php

namespace Netflex\Commerce;

use Netflex\Commerce\Traits\API\PaymentItemAPI;
use Netflex\Support\ReactiveObject;

class PaymentItem extends ReactiveObject
{
  use PaymentItemAPI;

  protected $readOnlyAttributes = [
    'id',
    'order_id'
  ];

  protected $timestamps = [
    'payment_date'
  ];

  public function getOrderIdAttribute($value)
  {
    return (int) $value;
  }

  public function getAmountAttribute($value)
  {
    return (float) $value;
  }

  /**
   * @param mixed $data
   * @return Properties
   */
  public function getDataAttribute($data)
  {
    return Properties::factory($data, $this)
      ->addHook('modified', function ($data) {
        $this->__set('data', $data->jsonSerialize());
      });
  }

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    $json = parent::jsonSerialize();
    $json['data'] = $this->data->jsonSerialize();

    return $json;
  }
}
