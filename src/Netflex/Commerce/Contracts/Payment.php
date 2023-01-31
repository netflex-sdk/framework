<?php

namespace Netflex\Commerce\Contracts;

use DateTimeInterface;

interface Payment extends Lockable
{
    public function getPaymentMethod(): string;
    public function getPaymentStatus(): string;
    public function getCaptureStatus(): string;
    public function getTransactionId(): string;
    public function getCardType(): ?string;
    public function getMaskedCardNumber(): ?string;
    public function getCardExpiry(): ?DateTimeInterface;
    public function getPaymentAmount(): float;
    public function getPaymentDate(): DateTimeInterface;
    public function getIsPending(): bool;
}
