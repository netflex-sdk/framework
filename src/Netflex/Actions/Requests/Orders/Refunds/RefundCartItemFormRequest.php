<?php

namespace Netflex\Actions\Requests\Orders\Refunds;

use Illuminate\Foundation\Http\FormRequest;
use Netflex\Commerce\Exceptions\OrderNotFoundException;
use Netflex\Commerce\Order;

class RefundCartItemFormRequest extends FormRequest
{
    private ?Order $order;

    public function rules()
    {
        return [
            'order_id' => ['required', 'numeric'],
            'cart_item_id' => ['required', 'numeric']
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
}
