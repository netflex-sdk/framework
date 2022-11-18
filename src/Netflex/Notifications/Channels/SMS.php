<?php

namespace Netflex\Notifications\Channels;

use Exception;

use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SMS
{
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->no_sms) {
            if (!method_exists($notification, 'toSMS')) {
                throw new Exception('Method ' . get_class($notification)  . '::toSMS() not implemented');
            }

            $from = method_exists($notification, 'from') ? $notification->from() : Variable::get('sms_from');
            $to = ($notifiable->phone_countrycode ? ('+' . $notifiable->phone_countrycode) : null) . $notifiable->phone;
            $message = $notification->toSMS($notifiable);

            if (method_exists($notifiable, 'resolvePhoneNumber')) {
                $resolvedPhoneNumber = $notifiable->resolvePhoneNumber($notifiable);
                if ($resolvedPhoneNumber && array_key_exists('countrycode', $resolvedPhoneNumber) && array_key_exists('phone', $resolvedPhoneNumber)) {
                    $countrycode = $resolvedPhoneNumber['countrycode'];
                    if (!Str::startsWith($countrycode, '+')) {
                        $countrycode = '+' . $countrycode;
                    }
                    $phone = $resolvedPhoneNumber['phone'];

                    if ($countrycode && $phone) {
                        $to = $countrycode . $resolvedPhoneNumber['phone'];
                    }
                }
            }

            API::post('relations/sms/send/single', [
                'to' => [$to],
                'content' => $message,
                'sender' => $from
            ]);
        }
    }
}
