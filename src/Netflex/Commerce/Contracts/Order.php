<?php

namespace Netflex\Commerce\Contracts;

use DateTimeInterface;

use Netflex\Commerce\Contracts\Payment;
use Netflex\Commerce\Contracts\CartItem;

interface Order extends Lockable
{
    public function getOrderId(): ?int;
    public function getOrderSecret(): ?string;

    public function getOrderCustomerEmail(): ?string;
    public function setOrderCustomerEmail(?string $email): void;

    public function getOrderCustomerPhone(): ?string;
    public function setOrderCustomerPhone(?string $phone): void;

    public function getOrderCustomerFirstname(): ?string;
    public function setOrderCustomerFirstname(?string $firstname): void;

    public function getOrderCustomerSurname(): ?string;
    public function setOrderCustomerSurname(?string $surname): void;

    public function getOrderTax(): float;
    public function getOrderSubtotal(): float;
    public function getOrderTotal(): float;

    /**
     * @param string $key
     * @return mixed
     */
    public function getOrderData(string $key);
    public function setOrderData(string $key, $value, ?string $label = null): void;

    public function getOrderStatus(): string;
    public function setOrderStatus(string $status): void;

    public function getOrderCheckoutStart(): DateTimeInterface;
    public function setOrderCheckoutStart(DateTimeInterface $date): void;

    public function getOrderCheckoutEnd(): DateTimeInterface;
    public function setOrderCheckoutEnd(DateTimeInterface $date): void;

    /** @return CartItem[] */
    public function getOrderCartItems(): array;

    public function addOrderCartItem(CartItem $cartItem);

    public function deleteOrder(): void;

    public function saveOrder();

    public function refreshOrder(): Order;

    /** @return string|int */
    public function getOrderReceiptId();

    /** @return int|string */
    public function getCustomerId();

    public function getOrderCurrency(): string;

    public function registerPayment(Payment $payment): void;

    public function registerOrder();

    public function checkoutOrder();

    public function getTotalPaid(): float;

    public function getPaymentMethod(): ?string;

    public function lockOrder();

    public function addOrderDiscount(Discount $discount);

    /** @return Payment[] */
    public function getOrderPayments(): array;

    public function isCartMutable(): bool;

    public function deletePayment(Payment $payment): ?Payment;
    public function updatePayment(Payment $payment): ?Payment;

    public function isCompletable(): bool;
    public function isCompleted(): bool;

    public function canBeCompleted(): bool;
    public function completeOrder();

}
