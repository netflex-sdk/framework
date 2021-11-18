<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;

/**
 * @property-read int $id
 * @property-read CartItemCollection $items
 * @property-read ReservationItemCollection $reservations
 * @property-read float $discount
 * @property-read float $tax
 * @property-read float $total
 * @property-read float $weight
 * @property-read int $count_items
 * @property-read int $count_lines
 * @property-read float $sub_total
 */
class Cart extends ReactiveObject
{
  protected $defaults = [
    'sub_total' => 0,
    'discount' => 0,
    'tax' => 0,
    'total' => 0,
    'weight' => 0,
    'count_items' => 0,
    'count_lines' => 0,
    'items' => [],
    'reservations' => [],
  ];

  protected $readOnlyAttributes = [
    'sub_total',
    'discount',
    'tax',
    'total',
    'weight',
    'count_items',
    'count_lines',
    'reservations'
  ];

  /**
   * @param mixed $items
   * @return CartItemCollection
   */
  public function getItemsAttribute($items = [])
  {
    if (!empty($items)) {
      $items = array_map(function ($item) {
        if (is_array($item)) {
          $item['order_id'] = $this->parent->id;
        } else {
          $item->order_id = $this->parent->id;
        }
        return $item;
      }, $items);
    }

    return CartItemCollection::factory($items, $this)
      ->addHook('modified', function ($items) {
        $this->__set('items', $items->jsonSerialize());
      });
  }

  /**
   * @param mixed $items
   * @return ReservationItemCollection
   */
  public function getReservationsAttribute($items = [])
  {
    return ReservationItemCollection::factory($items, $this)
      ->addHook('modified', function ($items) {
        $this->__set('reservations', $items->jsonSerialize());
      });
  }
}
