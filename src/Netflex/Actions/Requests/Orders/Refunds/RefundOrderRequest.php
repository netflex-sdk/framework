<?php

namespace Netflex\Actions\Requests\Orders\Refunds;

use Illuminate\Foundation\Http\FormRequest;
use Netflex\Commerce\Order;


class RefundOrderRequest extends FormRequest
{

    private ?Order $order;

    public function rules()
    {
        return [
            'order_id' => ['required', 'numeric'],
        ];
    }

    public function messages()
    {
        return [];
    }

    public function getOrder(): Order
    {
        $this->order ??= Order::retrieve($this->get('order_id'));
        return $this->order;
    }
}
