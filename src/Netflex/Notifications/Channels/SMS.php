<?php

namespace Netflex\Notifications\Channels;

use Exception;

use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Illuminate\Notifications\Notification;

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

            API::post('relations/sms/send/single', [
                'to' => [$to],
                'content' => $message,
                'sender' => $from
            ]);
        }
    }
}
