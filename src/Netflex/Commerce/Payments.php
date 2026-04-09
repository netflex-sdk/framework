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

  /** @var class-string<PaymentItemCollection> */
  const string PAYMENT_ITEM_COLLECTION_CLASS = PaymentItemCollection::class;

  protected array $defaults = [
    'total' => 0,
    'items' => []
  ];

  protected array $readOnlyAttributes = [
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
      static::PAYMENT_ITEM_COLLECTION_CLASS,
      'items',
      'itemsInstance',
    );
  }
}
