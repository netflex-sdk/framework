<?php

namespace Netflex\Commerce;

use Netflex\Query\Traits\HasRelation;
use Netflex\Query\Traits\ModelMapper;
use Netflex\Query\Traits\Queryable;
use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\OrderAPI;

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
class Order extends ReactiveObject
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
   * @param array $attributes
   * @return static
   */
  public function newFromBuilder($attributes = [])
  {
      try {
          $data = $attributes['data'] ?? [];
          if($class = $data['_class'])
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
}
