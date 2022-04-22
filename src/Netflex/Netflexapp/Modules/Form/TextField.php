<?php

namespace Netflex\Netflexapp\Modules\Form;

use App\Modules\BookingForm\BookingFormField;

class TextField extends BookingFormField
{
    function getType(): string
    {
        return "text";
    }
}
