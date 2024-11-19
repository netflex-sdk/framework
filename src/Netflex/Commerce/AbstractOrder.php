<?php

namespace Netflex\Commerce;

use Apility\Payment\Jobs\SendReceipt;
use Carbon\Carbon;
use DateTimeInterface;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Netflex\Commerce\Contracts\CartItem;
use Netflex\Commerce\Contracts\Order as OrderContract;
use Netflex\Commerce\Exceptions\CartNotMutableException;
use Netflex\Query\Traits\HasRelation;
use Netflex\Query\Traits\ModelMapper;
use Netflex\Query\Traits\Queryable;
use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\OrderAPI;
use Netflex\Signups\Signup;
use Netflex\Commerce\Contracts\Payment;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Netflex\Commerce\Contracts\Discount;
use Throwable;
use TypeError;

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
class AbstractOrder extends ReactiveObject implements OrderContract, UrlRoutable
{
    use OrderAPI;
    use Queryable;
    use HasRelation;
    use ModelMapper;
    use HasEvents;



    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected static $dispatcher;

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
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
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
     * @param string $childType
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
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
        } catch (Throwable $t) {
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
        return $id ? (int)$id : $id;
    }

    /**
     * @param string|float|int $tax
     * @return float
     */
    public function getOrderTaxAttribute($tax)
    {
        return (float)$tax;
    }

    /**
     * @param string|float|int $cost
     * @return float
     */
    public function getOrderCostAttribute($cost)
    {
        return (float)$cost;
    }

    /**
     * @param string|float|int $total
     * @return float
     */
    public function getOrderTotalAttribute($total)
    {
        return (float)$total;
    }

    /**
     * @param string|int|boolean|null $abandoned
     * @return boolean
     */
    public function getAbandonedAttribute($abandoned)
    {
        return (bool)$abandoned;
    }

    /**
     * @param string|int|boolean|null $sent
     * @return boolean
     */
    public function getAbandonedReminderSentAttribute($sent)
    {
        return (bool)$sent;
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
        return (float)$this->order_tax;
    }

    public function getOrderSubtotal(): float
    {
        return (float)$this->order_cost;
    }

    public function getOrderTotal(): float
    {
        return (float)$this->order_total;
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

    /**
     * @param CartItem $cartItem
     * @return void
     * @throws CartNotMutableException
     * @throws Exception
     */
    public function addOrderCartItem(CartItem $cartItem)
    {
        if (!$this->isCartMutable()) {
            throw new CartNotMutableException();
        }

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

    public function getTotalPaid(): float
    {
        return (float)$this->payments->total;
    }

    public function getPaymentMethod(): ?string
    {
        return collect($this->getOrderPayments())
            ->reject(fn(Payment $p) => $p->getIsPending())
            ->reject(fn(Payment $p) => $p->getPaymentAmount() == 0)
            ->map(fn(Payment $p) => $p->getCardType())
            ->unique()
            ->filter()
            ->join(", ");
    }

    public function checkoutOrder()
    {
        $this->checkout(['checkout_end' => Carbon::now()->toDateString()]);
    }

    public function registerOrder()
    {
        $this->register();
    }

    /**
     * @throws Exception
     */
    public function registerPayment(Payment $payment): void
    {
        $this->logPaymentChange('Creating', $payment);
        $this->addPayment([
            'payment_method' => $payment->getPaymentMethod(),
            'status' => $payment->getPaymentStatus(),
            'capture_status' => $payment->getCaptureStatus(),
            'transaction_id' => $payment->getTransactionId(),
            'card_type_name' => $payment->getCardType(),
            'amount' => $payment->getPaymentAmount(),
            'data' => [
                'isLocked' => $payment->isLocked()
            ]
        ]);
        $this->save();
        $this->refreshOrder();
    }

    public function lockOrder()
    {
        $this->lock();
    }

    public function addOrderDiscount(Discount $item)
    {
        if ($item->getDiscountType() !== Discount::TYPE_PERCENTAGE) {
            throw new TypeError('Only percentage discounts are supported at the cart scope');
        }

        $this->addDiscount([
            'scope' => 'cart',
            'discount_id' => $item->getDiscountId(),
            'discount' => $item->getDiscountValue(),
            'label' => $item->getDiscountLabel(),
            'type' => $item->getDiscountType(),
        ]);

        $this->refresh();
    }

    public function getOrderPayments(): array
    {
        return $this->payments->items->all();
    }


    /**
     *
     * Checks if the cart is mutable by checking first if the order is mutable
     * and then checks payments for any payment that is considered reserved or captured.
     *
     * @return bool Returns true if the cart is mutable
     */
    public function isCartMutable(): bool
    {
        return !$this->isLocked() && collect($this->getOrderPayments())
            ->reject(fn(Payment $payment) => $payment->getIsPending())
            ->count() === 0;
    }

    public function isLocked(): bool
    {
        return ($this->data->_mutable ?? "0") || ($this->data->_immutable ?? "0");
    }

    public function setLocked(bool $isLocked)
    {
        $this->setOrderData('_immutable', $isLocked);
    }

    /**
     * @throws Exception
     */
    public function updatePayment(Payment $payment): ?Payment
    {
        $this->logPaymentChange('Updating', $payment);
        $updatePayment = collect($this->getOrderPayments())
            ->first(fn(Payment $existingPayment) => $existingPayment->getTransactionId() === $payment->getTransactionId());

        if ($updatePayment) {
            /** @var PaymentItem $updatePayment */
            $updatePayment->save([
                'payment_method' => $payment->getPaymentMethod(),
                'status' => $payment->getPaymentStatus(),
                'capture_status' => $payment->getCaptureStatus(),
                'transaction_id' => $payment->getTransactionId(),
                'card_type_name' => $payment->getCardType(),
                'amount' => $payment->getPaymentAmount(),
                'data' => [
                    'isLocked' => $payment->isLocked()
                ]
            ]);
        }

        return $updatePayment;
    }

    /**
     * @throws Exception
     */
    public function deletePayment(Payment $payment): ?Payment
    {
        $this->logPaymentChange('Deleting', $payment);
        $deletePayment = $this->getOrderPayments()
            ->first(fn(Payment $existingPayment) => $existingPayment->getTransactionId() === $payment->getTransactionId());

        if ($deletePayment) {
            /** @var PaymentItem $deletePayment */
            $deletePayment->delete();
        }

        return $deletePayment;
    }

    public function isCompleted(): bool
    {
        return !!($this->register && $this->register->receipt_order_id);
    }

    public function isCompletable(): bool
    {
        return $this->cart->count_items > 0 && $this->getTotalPaid() >= $this->getOrderTotal();
    }

    /**
     * @param Payment $payment
     * @return void
     * @throws Exception
     */
    public function logPaymentChange(string $eventType, Payment $payment): void
    {
        if (!config('app.debug', false)) {
            return;
        }

        try {
            /** @noinspection PhpComposerExtensionStubsInspection */
            $encode = json_encode($payment, JSON_PRETTY_PRINT);
        } catch (Throwable $t) {
            $encode = "Unable to json serialize payment";
        }
        try {
            $this->addLogInfo("[{$payment->getPaymentMethod()}] $eventType payment\r\n<br><pre>{$encode}<\pre>");
        } catch (Throwable $t) {
            Log::error('Failed to log: ' . $t, [
                'secret' => $this->secret,
                'error' => $t
            ]);
        }
    }

    public function canBeCompleted(): bool
    {
        return $this->isCompletable() && !$this->isCompleted() && !$this->isLocked();
    }

    public function completeOrder()
    {
        $this->checkoutOrder();
        $this->registerOrder();
        $this->lockOrder();
        $this->refreshOrder();
    }

    public function __serialize(): array
    {
        return ['id' => $this->id];
    }

    public function __unserialize(array $data)
    {
        $this->attributes = $data;
        $this->refresh();
    }
}
