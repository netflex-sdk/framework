<?php

namespace Netflex\Netflexapp\Controllers\Orders\Refunds;

use Netflex\Commerce\CartItem;
use Netflex\Commerce\Order;
use Netflex\Netflexapp\Contracts\Orders\Refunds\FormDescriber;
use Netflex\Netflexapp\Requests\Orders\Refunds\RefundOrderFormRequest;

abstract class FormController
{
    /**
     *
     * Receives the order id and resolves a
     *
     * @param int $order_id
     * @return Order
     */
    abstract function resolveFormDescriber(Order $order_id, ?CartItem $cartItem): FormDescriber;


    public function renderRefundOrderForm(RefundOrderFormRequest $r)
    {

        $order = $r->getOrder();

        $resolver = $this->resolveFormDescriber($order, null);

        abort_unless(!!$order, 404, "Order not found");

        /** @var Order $order */
        return response()->json($resolver->refundOrderForm($order));

    }

    public function renderRefundCartItemForm(RefundOrderFormRequest $r)
    {
        $order = $r->getOrder();
        $cartItem = $r->getCartItem();
        $resolver = $this->resolveFormDescriber($order, $cartItem);

        abort_unless(!!$order, 404, "Order not found");
        abort_unless(!!$cartItem, 404, "Cart item not found");

        /** @var Order $order */
        return response()->json($resolver->refundCartItemForm($order, $cartItem));
    }
}
