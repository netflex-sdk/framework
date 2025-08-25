<?php

namespace Netflex\Actions\Requests\Orders\Refunds;

use Illuminate\Foundation\Http\FormRequest;

class RefundOrderFormRequest extends FormRequest
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
