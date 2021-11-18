<?php

namespace Netflex\Notifications\Channels;

use Exception;

use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;

use Illuminate\Notifications\Notification;

class SMS {
    public function send ($notifiable, Notification $notification) {
        if (!$notifiable->no_sms) {
            if (!method_exists($notification, 'toSMS')) {
                throw new Exception('Method ' . get_class($notification)  . '::toSMS() not implemented');
            }

            API::post('relations/sms/send/single', [
                'to' => [
                    ($notifiable->phone_countrycode ? ('+' . $notifiable->phone_countrycode) : null) . $notifiable->phone
                ],
                'content' => $notification->toSMS($notifiable),
                'sender' => Variable::get('mail_sender_name')
            ]);
        }
    }
}
