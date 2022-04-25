<?php

namespace Netflex\Actions\Requests\Orders\Refunds;

use Netflex\Commerce\Order;

class RefundOrderFormRequest extends \Illuminate\Foundation\Http\FormRequest
{
    public function rules()
    {
        return [
            'order_id' => ['required', 'numeric']
        ];
    }

    public function messages()
    {
        return [];
    }
}
