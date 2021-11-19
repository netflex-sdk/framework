<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;

/**
 * @property-read float $total
 * @property PaymentItemCollection $items
 */
class Payments extends ReactiveObject
{
  protected $defaults = [
    'total' => 0,
    'items' => []
  ];

  protected $readOnlyAttributes = [
    'total',
    'items'
  ];

  /**
   * @param array|null $items
   * @return PaymentItemCollection
   */
  public function getItemsAttribute($items = [])
  {
    return PaymentItemCollection::factory($items, $this)
      ->addHook('modified', function ($items) {
        $this->__set('items', $items->jsonSerialize());
      });
  }
}
