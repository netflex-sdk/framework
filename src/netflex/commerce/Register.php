<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;

/**
 * @property-read int $receipt_order_id
 * @property-read int $receipt_shipping_id
 */
class Register extends ReactiveObject
{
  public function getReceiptOrderIdAttribute($id)
  {
    return (int) $id;
  }

  public function getReceiptShippingIdAttribute($id)
  {
    return (int) $id;
  }
}
