<?php

namespace Netflex\Notifications;

use Netflex\Customers\Customer;
use Illuminate\Notifications\Notifiable;

final class AnonymousUser extends Customer
{
    use Notifiable;

    public function __construct($to)
    {
        parent::__construct();
        $this->phone = $to;
    }

    public static function make($to)
    {
        if (is_object($to) && in_array(Notifiable::class, class_uses_recursive($to))) {
            return $to;
        }

        return new static($to);
    }
}
