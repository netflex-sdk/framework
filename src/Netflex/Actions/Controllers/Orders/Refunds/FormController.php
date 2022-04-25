<?php

namespace Netflex\Actions\Controllers\Orders\Refunds;

use Netflex\Commerce\CartItem;
use Netflex\Commerce\Exceptions\OrderNotFoundException;
use Netflex\Commerce\Order;
use Netflex\Actions\Contracts\Orders\Refunds\FormDescriber;
use Netflex\Actions\Requests\Orders\Refunds\RefundOrderFormRequest;

abstract class FormController
{
    /**
     *
     * Receives the order and cart item and determines which form describer to use. This is a feature that lets you
     * support multiple types of forms for orders of the same type.
     *
     * @param Order $order
     * @return FormDescriber
     */
    abstract function resolveFormDescriber(Order $order, ?CartItem $cartItem): FormDescriber;


    /**
     *
     * Overridable function that resolves the order object from the order_id supplied by the request.
     *
     * The order is supposed to be polymorphically resolved in newer projects but this function can be overwritten
     * if custom order resolving is necessary
     *
     * @param $order_id
     * @return Order|null
     */
    protected function resolveOrder($order_id): ?Order
    {
        try {
            return Order::retrieve($order_id);
        } catch (OrderNotFoundException $e) {
            return null;
        }
    }

    protected function resolveCartItem(Order $order, $cart_item_id): ?CartItem
    {
        return $order->cart->items->first(fn (CartItem $cartItem) => $cartItem->id == $cart_item_id);
    }

    public function renderRefundOrderForm(RefundOrderFormRequest $r)
    {

        $order = $this->resolveOrder($r->get('order_id'));
        abort_unless(!!$order, 404, "Order not found");

        $resolver = $this->resolveFormDescriber($order, null);

        /** @var Order $order */
        return response()->json($resolver->refundOrderForm($order));
    }

    public function renderRefundCartItemForm(RefundOrderFormRequest $r)
    {
        $order = $this->resolveOrder($r->get('order_id'));
        abort_unless(!!$order, 404, "Order not found");

        $cartItem = $this->resolveCartItem($order, $r->get('cart_item_id'));
        abort_unless(!!$cartItem, 404, "Cart item not found");

        $resolver = $this->resolveFormDescriber($order, $cartItem);

        /** @var Order $order */
        return response()->json($resolver->refundCartItemForm($order, $cartItem));
    }
}
