<?php

namespace Netflex\Commerce;

use Illuminate\Support\Collection;
use Netflex\Commerce\Traits\Reactivity\HasReactiveChildrenProperties;
use Netflex\Support\ReactiveObject;

/**
 * @property-read float $total
 * @property PaymentItemCollection $items
 */
class Payments extends ReactiveObject
{
  use HasReactiveChildrenProperties;

  protected $defaults = [
    'total' => 0,
    'items' => []
  ];

  protected $readOnlyAttributes = [
    'total',
    'items'
  ];

  protected ?PaymentItemCollection $itemsInstance;

  public function setItemsAttribute(Collection|array|null $items): void
  {
    $this->setItemCollection($items, 'items', 'itemsInstance');
  }

  public function getItemsAttribute(
    array|null $items = null,
  ): PaymentItemCollection {
    return $this->getItemCollection(
      $items,
      PaymentItemCollection::class,
      'items',
      'itemsInstance',
    );
  }
}
