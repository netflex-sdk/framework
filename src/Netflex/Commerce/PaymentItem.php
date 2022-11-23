<?php

namespace Netflex\Commerce;


use DateTimeInterface;

use Illuminate\Support\Carbon;

use Netflex\Commerce\Contracts\Payment;
use Netflex\Commerce\Traits\API\PaymentItemAPI;
use Netflex\Support\ReactiveObject;

class PaymentItem extends ReactiveObject implements Payment
{
  use PaymentItemAPI;

  protected $readOnlyAttributes = [
    'id',
    'order_id'
  ];

  protected $timestamps = [
    'payment_date'
  ];

  public function getPaymentMethod(): string
  {
    return $this->payment_method;
  }

  public function getPaymentStatus(): string
  {
    return $this->status;
  }

  public function getCaptureStatus(): string
  {
    return $this->capture_status;
  }

  public function getTransactionId(): string
  {
    return $this->transaction_id;
  }

  public function getCardType(): ?string
  {
    return $this->card_type_name;
  }

  public function getMaskedCardNumber(): ?string
  {
    return $this->data->masked_card_number ?? null;
  }

  public function getCardExpiry(): ?DateTimeInterface
  {
    if ($this->data->card_expiry ?? null) {
      return Carbon::parse($this->data->card_expiry);
    }
  }

  public function getPaymentAmount(): float
  {
    return (float) $this->amount;
  }

  public function getPaymentDate(): DateTimeInterface
  {
    return Carbon::parse($this->payment_date);
  }

  public function getOrderIdAttribute($value)
  {
    return (int) $value;
  }

  public function getAmountAttribute($value)
  {
    return (float) $value;
  }

  /**
   * @param mixed $data
   * @return Properties
   */
  public function getDataAttribute($data)
  {
    return Properties::factory($data, $this)
      ->addHook('modified', function ($data) {
        $this->__set('data', $data->jsonSerialize());
      });
  }

  /**
   * @return array
   */
  public function jsonSerialize()
  {
    $json = parent::jsonSerialize();
    $json['data'] = $this->data->jsonSerialize();

    return $json;
  }
}
