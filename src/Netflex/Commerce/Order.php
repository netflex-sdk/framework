<?php

namespace Netflex\Commerce;

use DateTimeInterface;

use Netflex\Commerce\Contracts\CartItem;
use Netflex\Commerce\Contracts\Order as OrderContract;

use Netflex\Query\Traits\HasRelation;
use Netflex\Query\Traits\ModelMapper;
use Netflex\Query\Traits\Queryable;
use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\OrderAPI;
use Netflex\Signups\Signup;

use Illuminate\Contracts\Routing\UrlRoutable;;

/**
 * @property-read int $id
 * @property-read string $secret
 * @property float $order_tax
 * @property float $order_cost
 * @property float $order_total
 * @property int $customer_id
 * @property string $customer_code
 * @property string $customer_mail
 * @property string $customer_phone
 * @property string $created
 * @property string $updated
 * @property boolean $abandoned
 * @property boolean $abandoned_reminder_sent
 * @property string $abandoned_reminder_mail
 * @property-read Register $register
 * @property string $status
 * @property string $type
 * @property string $ip
 * @property string $user_agent
 * @property-read Cart $cart
 * @property-read Payments $payments
 * @property-read Data $data
 * @property-read LogItemCollection $log
 * @property-read Checkout $checkout
 * @property-read DiscountItemCollection $discounts
 */
class Order extends ReactiveObject implements OrderContract, UrlRoutable
{
  use OrderAPI;
  use Queryable;
  use HasRelation;
  use ModelMapper;

  public static $useCache = true;
  public static $cacheBaseKey = 'order';
  public static $cacheTTL = 3600; // seconds

  public static $sessionKey = 'netflex_cart';

  protected static $base_path = 'commerce/orders';

  protected $triedReceivedBySession = false;

  protected $respectPublishingStatus = false;

  protected $relation = 'order';

  protected $defaults = [
    'id' => null,
    'created' => null,
    'updated' => null,
    'status' => null,
    'type' => null,
    'secret' => null,
    'ip' => null,
    'user_agent' => null,
    'customer_id' => null,
    'customer_code' => null,
    'customer_mail' => null,
    'customer_phone' => null,
    'payment_method' => null,
    'currency' => null,
    'order_cost' => 0,
    'order_total' => 0,
    'order_tax' => 0,
    'abandoned' => false,
    'abandoned_reminder_sent' => false,
    'abandoned_reminder_mail' => null,
    'register' => null,
    'data' => [
      '_class' => self::class,
    ],
    'cart' => null,
    'log' => null,
    'checkout' => null,
    'payments' => null,
    'discounts' => null,
  ];

  /**
   * Get the value of the model's route key.
   *
   * @return mixed
   */
  public function getRouteKey()
  {
    return $this->{$this->getRouteKeyName()};
  }

  /**
   * Get the route key for the model.
   *
   * @return string
   */
  public function getRouteKeyName()
  {
    return 'secret';
  }

  /**
   * Retrieve the model for a bound value.
   *
   * @param  mixed  $value
   * @param  string|null  $field
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function resolveRouteBinding($value, $field = null)
  {
    if ($field === null) {
      $field = $this->getRouteKeyName();
    }

    if ($field === 'secret') {
      return static::retrieveBySecret($value);
    }

    return static::where($field, $value)->first();
  }

  /**
   * Retrieve the child model for a bound value.
   *
   * @param  string  $childType
   * @param  mixed  $value
   * @param  string|null  $field
   * @return \Illuminate\Database\Eloquent\Model|null
   */
  public function resolveChildRouteBinding($childType, $value, $field)
  {
    return $this->resolveRouteBinding($value, $field);
  }

  /**
   * @param array $attributes
   * @return static
   */
  public function newFromBuilder($attributes = [])
  {
    try {
      $data = $attributes['data'] ?? [];
      if ($class = $data['_class'] ?? null)
        return new $class($attributes);
      else
        return new static($attributes);
    } catch (\Throwable $t) {
      return new static($attributes);
    }
  }

  public function usesChunking()
  {
    return $this->useChunking ?? false;
  }

  /**
   * @param string|int $id
   * @return int|null
   */
  public function getCustomerIdAttribute($id)
  {
    return $id ? (int) $id : $id;
  }

  /**
   * @param string|float|int $tax
   * @return float
   */
  public function getOrderTaxAttribute($tax)
  {
    return (float) $tax;
  }

  /**
   * @param string|float|int $cost
   * @return float
   */
  public function getOrderCostAttribute($cost)
  {
    return (float) $cost;
  }

  /**
   * @param string|float|int $total
   * @return float
   */
  public function getOrderTotalAttribute($total)
  {
    return (float) $total;
  }

  /**
   * @param string|int|boolean|null $abandoned
   * @return boolean
   */
  public function getAbandonedAttribute($abandoned)
  {
    return (bool) $abandoned;
  }

  /**
   * @param string|int|boolean|null $sent
   * @return boolean
   */
  public function getAbandonedReminderSentAttribute($sent)
  {
    return (bool) $sent;
  }

  /**
   * @param object|array|null $register
   * @return Register
   */
  public function getRegisterAttribute($register)
  {
    return Register::factory($register, $this);
  }

  /**
   * @param object|array|null $cart
   * @return Cart
   */
  public function getCartAttribute($cart = [])
  {
    return Cart::factory($cart, $this)
      ->addHook('modified', function (Cart $cart) {
        $this->__set('cart', $cart->jsonSerialize());
      });
  }

  /**
   * @param object|array|null $data
   * @return Data
   */
  public function getDataAttribute($data = [])
  {
    return Data::factory($data, $this)
      ->addHook('modified', function (Data $data) {
        $this->__set('data', $data->jsonSerialize());
      });
  }

  /**
   * @param object|array|null $payments
   * @return Payments
   */
  public function getPaymentsAttribute($payments = null)
  {
    return Payments::factory($payments, $this)
      ->addHook('modified', function (Payments $payments) {
        $this->__set('payments', $payments->jsonSerialize());
      });
  }

  /**
   * @param object|array|null $checkout
   * @return Checkout
   */
  public function getCheckoutAttribute($checkout = null)
  {
    return Checkout::factory($checkout, $this)
      ->addHook('modified', function (Checkout $checkout) {
        $this->__set('checkout', $checkout->jsonSerialize());
      });
  }

  /**
   * @param array|null $discounts
   * @return DiscountItemCollection
   */
  public function getDiscountsAttribute($discounts = [])
  {
    return DiscountItemCollection::factory($discounts, $this)
      ->addHook('modified', function (DiscountItemCollection $discounts) {
        $this->__set('discounts', $discounts->jsonSerialize());
      });
  }

  /**
   * @param array|null $log
   * @return LogItemCollection
   */
  public function getLogAttribute($log = [])
  {
    return LogItemCollection::factory($log, $this)
      ->addHook('modified', function (LogItemCollection $log) {
        $this->__set('log', $log->jsonSerialize());
      });
  }

  /**
   * Return the current date and time, formatted as it is stored in the database
   * @return string
   */
  public static function dateTimeNow()
  {
    return date('Y-m-d H:i:s');
  }

  /**
   * @return string
   */
  public static function basePath()
  {
    return trim(static::$base_path, '/') . '/';
  }

  public function getOrderId(): ?int
  {
    return $this->id;
  }

  public function getOrderSecret(): ?string
  {
    return $this->secret;
  }

  public function getOrderCustomerEmail(): ?string
  {
    return $this->customer_mail;
  }

  public function setOrderCustomerEmail(?string $email): void
  {
    $this->customer_mail = $email;
  }

  public function getOrderCustomerPhone(): ?string
  {
    return $this->customer_phone;
  }

  public function setOrderCustomerPhone(?string $phone): void
  {
    $this->customer_phone = $phone;
  }

  public function getOrderCustomerFirstname(): ?string
  {
    return $this->checkout->firstname;
  }

  public function setOrderCustomerFirstname(?string $firstname): void
  {
    $this->checkout(['firstname' => $firstname]);
  }

  public function getOrderCustomerSurname(): ?string
  {
    return $this->checkout->surname;
  }

  public function setOrderCustomerSurname(?string $surname): void
  {
    $this->checkout(['surname' => $surname]);
  }

  public function getOrderTax(): float
  {
    return $this->order_tax;
  }

  public function getOrderSubtotal(): float
  {
    return $this->cart->subtotal;
  }

  public function getOrderTotal(): float
  {
    return $this->order_total;
  }

  public function getOrderData(string $key)
  {
    return $this->data[$key] ?? null;
  }

  public function setOrderData(string $key, $value, ?string $label = null): void
  {
    $this->addData($key, $value, $label ?? $key);
  }

  public function getOrderStatus(): string
  {
    return $this->status;
  }

  public function setOrderStatus(string $status): void
  {
    $this->saveStatus($status);
  }

  public function getOrderCartItems(): array
  {
    return $this->cart->items->all();
  }

  public function addOrderCartItem(CartItem $cartItem)
  {
    $this->addCart([
      'entry_id' => $cartItem->getCartItemProductId(),
      'entry_name' => $cartItem->getCartItemProductName(),
      'no_of_entries' => $cartItem->getCartItemQuantity(),
      'variant_id' => $cartItem->getCartItemVariantId(),
      'variant_name' => $cartItem->getCartItemVariantName(),
      'variant_cost' => $cartItem->getCartItemPrice(),
      'tax_percent' => $cartItem->getCartItemTaxRate(),
      'ip' => request()->ip(),
      'user_agent' => request()->userAgent(),
      'properties' => $cartItem->getCartItemProperties(),
    ]);
  }

  public function saveOrder(): void
  {
    $this->save();
  }

  public function deleteOrder(): void
  {
    $this->delete();
  }

  public function getOrderCheckoutStart(): DateTimeInterface
  {
    return $this->checkout->checkout_start;
  }

  public function setOrderCheckoutStart(DateTimeInterface $date): void
  {
    $this->checkout(['checkout_start' => $date->format('Y-m-d H:i:s')]);
  }

  public function getOrderCheckoutEnd(): DateTimeInterface
  {
    return $this->checkout->checkout_end;
  }

  public function setOrderCheckoutEnd(DateTimeInterface $date): void
  {
    $this->checkout(['checkout_end' => $date->format('Y-m-d H:i:s')]);
  }

  public function refreshOrder(): OrderContract
  {
    return $this->refresh();
  }

  public function getOrderReceiptId()
  {
    return $this->register->receipt_order_id;
  }

  public function getSignupsAttribute()
  {
    return Signup::forOrder($this);
  }

  public function getCustomerId()
  {
    return $this->customer_id;
  }

  public function getOrderCurrency(): string
  {
    return $this->currency ?? 'NOK';
  }
}
