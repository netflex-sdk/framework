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

}
