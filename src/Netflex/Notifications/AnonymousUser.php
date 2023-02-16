<?php

namespace Netflex\Notifications;

use Netflex\Customers\Customer;
use Illuminate\Notifications\Notifiable;
use ReflectionClass;

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

        if ($to instanceof Customer) {
            $reflection = new ReflectionClass($to);
            $property = $reflection->getProperty('attributes');
            $property->setAccessible(true);
            $notifiable = new static;
            return $notifiable->newFromBuilder($property->getValue($to));
        }

        return new static;
    }

    public static function fromMail($to): AnonymousUser
    {
        $user = static::make($to);

        if (is_string($to)) {
            $user->mail = $to;
        }

        return $user;
    }

    public static function fromPhone($to): AnonymousUser
    {
        $user = static::make($to);

        if (is_string($to)) {
            $user->phone = $to;
        }

        return $user;
    }
}
