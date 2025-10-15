<?php

namespace Netflex\Commerce;


use DateTimeInterface;

use Illuminate\Support\Carbon;

use Netflex\Commerce\Contracts\Payment;
use Netflex\Commerce\Traits\API\PaymentItemAPI;
use Netflex\Commerce\Traits\Reactivity\HasReactiveChildrenProperties;
use Netflex\Support\ReactiveObject;

/**
 *
 * @property $amount float
 * @property $payment_date string
 * @property $card_type_name string
 * @property $transaction_id string
 * @property $status string
 * @property $capture_status string
 * @property $payment_method string
 * @property $data Properties
 */
class PaymentItem extends ReactiveObject implements Payment
{
    use PaymentItemAPI;
    use HasReactiveChildrenProperties;

    const PROPERTIES_CLASS = Properties::class;

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

        return null;
    }

    public function getPaymentAmount(): float
    {
        return (float)$this->amount;
    }

    public function getPaymentDate(): DateTimeInterface
    {
        return Carbon::parse($this->payment_date);
    }

    public function getOrderIdAttribute($value)
    {
        return (int)$value;
    }

    public function getAmountAttribute($value)
    {
        return (float)$value;
    }

    protected ?Properties $propertiesInstance;

    public function setDataAttribute(object|array|null $data): void
    {
        $this->setReactiveObject(
            $data,
            'data',
            'propertiesInstance',
        );
    }

    public function getDataAttribute(
        object|array|null $data = null
    ): Properties {
        return $this->getReactiveObject(
            $data,
            static::PROPERTIES_CLASS,
            'data',
            'propertiesInstance',
        );
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['data'] = $this->data->jsonSerialize();

        return $json;
    }

    /**
     * Determines if the payment is paid by checking if the [capture_status] is reserved, captured or charged
     *
     * @return bool
     */
    public function getIsPending(): bool
    {
        return !in_array($this->getPaymentStatus(), ['paid', 'reserved']);
    }

    public function isLocked(): bool
    {
        return $this->data->isLocked ?? false;
    }

    public function setLocked(bool $isLocked)
    {
        $this->data->isLocked = $isLocked;
    }
}
