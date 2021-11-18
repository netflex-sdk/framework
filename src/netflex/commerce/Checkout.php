<?php

namespace Netflex\Commerce;

use Carbon\Carbon;
use Netflex\Support\ReactiveObject;

/**
 * @property-read int $id
 * @property-read int $order_id
 * @property Carbon $checkout_start
 * @property Carbon $checkout_end
 * @property string $firstname
 * @property string $surname
 * @property string $company
 * @property string $address
 * @property string $postal
 * @property string $city
 * @property string $shipping_firstname
 * @property string $shipping_surname
 * @property string $shipping_company
 * @property string $shipping_address
 * @property string $shipping_postal
 * @property string $shipping_city
 * @property float $shipping_cost
 * @property float $shipping_tax
 * @property float $shipping_total
 * @property float $expedition_cost
 * @property float $expedition_tax
 * @property float $expedition_total
 * @property string $shipping_tracking_code
 * @property string $shipping_tracking_url
 * @property string $ip
 * @property string $user_agent
 * @property Carbon $updated
 */
class Checkout extends ReactiveObject
{
  public function getOrderIdAttribute($value)
  {
    return (int) $value;
  }

  public function getShippingCostAttribute($value)
  {
    return (float) $value;
  }

  public function getShippingTaxAttribute($value)
  {
    return (float) $value;
  }

  public function getShippingTotalAttribute($value)
  {
    return (float) $value;
  }

  public function getExpeditionCostAttribute($value)
  {
    return (float) $value;
  }

  public function getExpeditionTaxAttribute($value)
  {
    return (float) $value;
  }

  public function getExpeditionTotalAttribute($value)
  {
    return (float) $value;
  }

  public function getCheckoutStartAttribute($value)
  {
    return !empty($value) ? new Carbon($value) : null;
  }

  public function getCheckoutEndAttribute($value)
  {
    return (!empty($value) && $value !== '0000-00-00 00:00:00') ? new Carbon($value) : null;
  }
}
