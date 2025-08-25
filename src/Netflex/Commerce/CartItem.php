<?php

namespace Netflex\Commerce;

use Carbon\Carbon;
use Netflex\Commerce\Traits\Reactivity\HasReactiveChildrenProperties;
use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\CartItemAPI;
use Netflex\Structure\Traits\Localizable;
use Netflex\Commerce\Contracts\CartItem as CartItemContract;
use Netflex\Commerce\Contracts\Discount;

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
 * @property-read int $order_id
 */
class CartItem extends ReactiveObject implements CartItemContract
{
  use CartItemAPI;
  use Localizable;
  use HasReactiveChildrenProperties;

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

  protected ?Properties $propertiesInstance;

  public function setPropertiesAttribute(object|array|null $properties): void
  {
    $this->setReactiveObject($properties, 'properties', 'propertiesInstance');
  }

  public function getPropertiesAttribute(
    object|array|null $properties = null
  ): Properties {
    return $this->getReactiveObject(
      $properties,
      Properties::class,
      'properties',
      'propertiesInstance',
    );
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
  #[\ReturnTypeWillChange]
  public function jsonSerialize()
  {
    $json = parent::jsonSerialize();
    $json['properties'] = $this->properties->jsonSerialize();

    return $json;
  }

  public function getCartItemLineNumber(): int
  {
    return $this->id;
  }

  public function getCartItemProductId(): int
  {
    return $this->entry_id;
  }

  public function setCartItemProductId(int $productId): void
  {
    $this->entry_id = $productId;
  }

  public function getCartItemProductName(): string
  {
    return $this->entry_name;
  }

  public function setCartItemProductName(string $productName): void
  {
    $this->entry_name = $productName;
  }

  public function getCartItemVariantId(): int
  {
    return (int) $this->variant_id;
  }

  public function setCartItemVariantId(int $variantId): void
  {
    $this->variant_id = $variantId;
  }

  public function getCartItemVariantName(): ?string
  {
    return $this->variant_name;
  }

  public function setCartItemVariantName(string $variantName): void
  {
    $this->variant_name = $variantName;
  }

  public function getCartItemQuantity(): int
  {
    return (int) $this->no_of_entries;
  }

  public function setCartItemQuantity(int $quantity): void
  {
    $this->no_of_entries = $quantity;
  }

  public function getCartItemPrice(): float
  {
    return (float) $this->variant_cost;
  }

  public function setCartItemPrice(float $price): void
  {
    $this->variant_cost = $price;
  }

  public function getCartItemTotal(): float
  {
    return (float) $this->entries_total;
  }

  public function getCartItemSubtotal(): float
  {
    return (float) $this->entries_cost;
  }

  public function getCartItemTax(): float
  {
    return (float) $this->tax_cost;
  }

  public function getCartItemTaxRate(): float
  {
    return (float) $this->tax_percent;
  }

  public function setCartItemTaxRate(float $taxRate): void
  {
    $this->tax_percent = $taxRate;
  }

  public function saveCartItem(): void
  {
    $this->save();
  }

  public function deleteCartItem(): void
  {
    $this->delete();
  }

  public function getCartItemProperty(string $key)
  {
    if ($this->properties->offsetExists($key)) {
      return $this->properties->offsetGet($key);
    }

    return null;
  }

  public function setCartItemProperty(string $key, $value): void
  {
    $properties = $this->properties;
    $properties->offsetSet($key, $value);
    $this->properties = $properties;
  }

  public function getCartItemProperties(): array
  {
    return $this->properties->toArray();
  }

  public function addCartItemDiscount(Discount $discount)
  {
    $this->addDiscount([
      'scope' => 'item',
      'scope_key' => $this->getCartItemLineNumber(),
      'discount_id' => $discount->getDiscountId(),
      'discount' => $discount->getDiscountValue(),
      'label' => $discount->getDiscountLabel(),
      'type' => $discount->getDiscountType(),
    ]);
  }
}
