<?php

namespace Netflex\Netflexapp\Requests\Orders\Refunds;

use Netflex\Commerce\Cart;
use Netflex\Commerce\CartItem;
use Netflex\Commerce\Exceptions\OrderNotFoundException;
use Netflex\Commerce\Order;

class RefundOrderFormRequest extends \Illuminate\Foundation\Http\FormRequest
{
    private ?Order $order;

    public function rules() {
        return [
            'order_id' => ['required', 'numeric']
        ];
    }

    public function messages() {
        return [];
    }

    public function getOrder(): ?Order {
        try {
            $this->order ??= Order::retrieve($this->get('order_id'));
            return $this->order;
        } catch(OrderNotFoundException $e) {
            return null;
        }
    }


    public function getCartItem(): ?CartItem {
        return $this->getOrder()->cart->items
            ->first(fn(CartItem $ci) => $ci->id == $this->get('cart_item_id'));
    }
}
