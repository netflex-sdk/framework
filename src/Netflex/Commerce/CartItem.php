<?php

namespace Netflex\Commerce;

use Carbon\Carbon;
use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\CartItemAPI;
use Netflex\Structure\Traits\Localizable;

/**
 * @property-read int $id
 * @property int $entry_id
 * @property string $entry_name
 * @property int $no_of_entries
 * @property string $type
 * @property int $variant_id
 * @property string $variant_value
 * @property string $variant_name
 * @property float $variant_cost
 * @property int $variant_weight
 * @property float $tax_percent
 * @property-read float $tax_cost
 * @property-read float $entries_cost
 * @property-read float $entries_total
 * @property int $entries_weight
 * @property-read Carbon $added
 * @property-read Carbon $updated
 * @property string $ip
 * @property string $user_agent
 * @property int $changed_in_cart
 * @property Carbon $reservation_start
 * @property Carbon $reservation_end
 * @property string $entries_comments
 * @property Properties $properties
 * @property-read float $entries_discount
 * @property-read DiscountData $discount_data
 * @property-read float $original_variant_cost
 * @property-read float $original_entries_total
 * @property-read float $original_entries_cost
 * @property-read float $original_tax_cost
 * @property-read id $order_id
 */
class CartItem extends ReactiveObject
{
  use CartItemAPI;
  use Localizable;

  protected $readOnlyAttributes = [
    'id',
    'tax_cost',
    'entries_cost',
    'entries_total',
    'added',
    'updated',
    'entries_discount',
    'discount_data',
    'original_variant_cost',
    'original_entries_total',
    'original_entries_cost',
    'original_tax_cost',
    'order_id',
  ];

  protected $defaults = [
    'entry_id' => null,
    'entry_name' => null,
    'variant_cost' => null,
    'tax_percent' => null,
    'no_of_entries' => null
  ];

  protected $timestamps = [
    'added',
    'updated'
  ];

  public function getEntryIdAttribute($value)
  {
    return (int) $value;
  }

  public function getNoOfEntriesAttribute($value)
  {
    return (int) $value;
  }

  public function getVariantIdAttribute($value)
  {
    return (int) $value;
  }

  public function getVariantCostAttribute($value)
  {
    return (float) $value;
  }

  public function getTaxPercentAttribute($value)
  {
    return (float) $value;
  }

  public function getTaxCostAttribute($value)
  {
    return (float) $value;
  }

  public function getEntriesCostAttribute($value)
  {
    return (float) $value;
  }

  public function getEntriesTotalAttribute($value)
  {
    return (float) $value;
  }

  public function getChangedInCartAttribute($value)
  {
    return (int) $value;
  }

  public function getReservationStartAttribute($value)
  {
    return !empty($value) ? new Carbon($value) : $value;
  }

  public function getReservationEndAttribute($value)
  {
    return !empty($value) ? new Carbon($value) : $value;
  }

  public function getOriginalVariantCostAttribute($value)
  {
    return (float) $value;
  }

  public function getOriginalEntriesTotalAttribute($value)
  {
    return (float) $value;
  }

  public function getOriginalEntriesCostAttribute($value)
  {
    return (float) $value;
  }

  public function getOriginalTaxCostAttribute($value)
  {
    return (float) $value;
  }

  /**
   * @param mixed $properties
   * @return Properties
   */
  public function getPropertiesAttribute($properties)
  {
    return Properties::factory($properties, $this)
      ->addHook('modified', function ($properties) {
        $this->__set('properties', $properties->jsonSerialize());
      });
  }

  /**
   * @param mixed $data
   * @return DiscountData
   */
  public function getDiscountDataAttribute($data)
  {
    return DiscountData::factory($data, $this);
  }

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    $json = parent::jsonSerialize();
    $json['properties'] = $this->properties->jsonSerialize();

    return $json;
  }
}
