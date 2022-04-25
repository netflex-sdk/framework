<?php

namespace Netflex\Actions\Modules\Form;

final class TextField extends BookingFormField
{
    public static function create(string $alias, string $label = "")
    {
        return new static($alias, $label);
    }

    function getType(): string
    {
        return "text";
    }
}
