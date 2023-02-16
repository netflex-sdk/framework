<?php

namespace Netflex\Notifications;

use Netflex\Customers\Customer;
use Illuminate\Notifications\Notifiable;

/**
 * @property-read string $email
 * @property-read string $phone
 * @property-read string $phone_countrycode
 */
final class AnonymousUser extends Customer
{
    use Notifiable;

    public function getEmailAttribute()
    {
        return $this->mail;
    }

    /**
     * @param string|Notifiable $to
     * @return AnonymousUser|Notifiable
     */
    public static function make($to)
    {
        if (is_object($to) && in_array(Notifiable::class, class_uses_recursive($to))) {
            return $to;
        }

        return new static;
    }

    public static function fromMail($to): AnonymousUser
    {
        $user = static::make($to);

        if ($user !== $to) {
            $user->mail = $to;
        }

        return $user;
    }

    public static function fromPhone($to): AnonymousUser
    {
        $user = static::make($to);

        if ($user !== $to) {
            $user->phone = $to;
        }

        return $user;
    }
}
