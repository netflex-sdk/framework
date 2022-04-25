<?php

namespace Netflex\Actions\Requests\Orders\Refunds;

use Illuminate\Foundation\Http\FormRequest;
use Netflex\Commerce\CartItem;
use Netflex\Commerce\Exceptions\OrderNotFoundException;
use Netflex\Commerce\Order;

class RefundCartItemRequest extends FormRequest
{
    private ?Order $order = null;

    public function rules()
    {
        return [
            'order_id' => 'required|numeric',
            'cart_item_id' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [];
    }

    public function getOrder(): ?Order
    {
        try {
            $this->order ??= Order::retrieve($this->get('order_id'));
            return $this->order;
        } catch (OrderNotFoundException $e) {
            return null;
        }
    }

    public function getCartItem(): ?CartItem
    {
        return $this->order->cart->items->first(fn (CartItem $ci) => $ci->id == $this->get('cart_item_id'));
    }
}
