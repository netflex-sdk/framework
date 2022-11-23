<?php

namespace Netflex\Commerce\Contracts;

interface Discount
{
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_AMOUNT = 'amount';
    const TYPE_FIXED = 'fixed';

    public function getDiscountId(): int;

    public function getDiscountValue(): float;

    public function getDiscountLabel(): ?string;

    public function getDiscountType(): string;
}
