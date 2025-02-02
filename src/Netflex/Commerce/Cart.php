<?php

namespace Netflex\Commerce;

use Illuminate\Support\Collection;
use Netflex\Commerce\Traits\Reactivity\HasReactiveChildrenProperties;
use Netflex\Support\ReactiveObject;

/**
 * @property-read int $id
 * @property CartItemCollection $items
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
  use HasReactiveChildrenProperties;

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

  protected ?CartItemCollection $itemsInstance;

  public function setItemsAttribute(Collection|array|null $items): void
  {
    $this->setItemCollection($items, 'items', 'itemsInstance');
  }

  public function getItemsAttribute(
    array|null $items = null,
  ): CartItemCollection {
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

    return $this->getItemCollection(
      $items,
      CartItemCollection::class,
      'items',
      'itemsInstance',
    );
  }

  protected ?ReservationItemCollection $reservationsInstance;

  /**
   * @param mixed $items
   * @return ReservationItemCollection
   */
  public function getReservationsAttribute(array|null $items = null)
  {
    return $this->getItemCollection(
      $items,
      ReservationItemCollection::class,
      'reservations',
      'reservationsInstance',
    );
  }
}
