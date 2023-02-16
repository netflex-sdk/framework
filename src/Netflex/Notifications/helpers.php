<?php

use Netflex\Customers\Customer;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Notifiable;
use Netflex\Notifications\AnonymousUser;
use Netflex\Notifications\GenericSmsNotifiation;

if (!function_exists('mustache')) {
    /**
     * Renders a Mustache template
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    function mustache(string $template, array $variables = [])
    {
        return with(new Mustache_Engine(['entitiy_flags' => ENT_QUOTES]))
            ->render($template, $variables);
    }
}

if (!function_exists('sms')) {
    /**
     * Sends an SMS message.
     *
     * @param mixed $to Either a phone number or a class that uses the Notifiable trait
     * @param string $message
     * @param string|null $from
     * @param array $data Optional data for template replacements
     * @return void
     */
    function sms($to, string $message, $from = null, array $data = [])
    {
        $notifiable = AnonymousUser::make($to);
        $notification = new GenericSmsNotifiation($message, $from, $data);
        return $notifiable->notify($notification);
    }
}
