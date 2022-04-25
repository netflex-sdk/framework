<?php

namespace Netflex\Actions\Contracts\Orders\Refunds;

use Netflex\Commerce\CartItem;
use Netflex\Commerce\Order;

interface FormDescriber
{
    public function refundOrderForm(Order $order): array;
    public function refundCartItemForm(Order $order, CartItem $cartItem): array;
}
