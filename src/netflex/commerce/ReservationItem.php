<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;

/**
 * @property int $cart_item
 * @property int $entry_id
 * @property int $variant_id
 * @property string $reservation_start
 * @property string $reservation_end
 * @property array|object $reservation_length
 */
class ReservationItem extends ReactiveObject
{
  protected $readOnlyAttributes = [
    'cart_item',
    'entry_id',
    'variant_id',
    'reservation_start',
    'reservation_end',
    'reservation_length'
  ];

  protected $timestamps = [
    'reservation_start',
    'reservation_end'
  ];

  public function getCartItemAttribute($value)
  {
    return (int) $value;
  }

  public function getEntryIdAttribute($value)
  {
    return (int) $value;
  }

  public function getVariantIdAttribute($value)
  {
    return (int) $value;
  }

  public function getReservationLengthAttribute($value)
  {
    return (object) $value;
  }
}
