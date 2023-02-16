<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

use Netflex\Notifications\AnonymousUser;
use Netflex\Notifications\GenericMail;
use Netflex\Notifications\GenericSmsNotification;

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
        $notifiable = AnonymousUser::fromPhone($to);

        if (!array_key_exists('notifiable', $data)) {
            $data['notifiable'] = $notifiable;
        }

        $notification = new GenericSmsNotification($message, $from, $data);
        $notifiable->notify($notification);
    }
}

if (!function_exists('notificaiton')) {
    /**
     * Sends an SMS message.
     *
     * @param mixed $to Either a phone number or a class that uses the Notifiable trait
     * @param string|View $message
     * @param string|null $subject
     * @param string|null $from
     * @param array $data Optional data for template replacements
     * @return void
     */
    function notification($to, $message, ?string $subject = null, array $data = [])
    {
        $notifiable = AnonymousUser::fromMail($to);
        $subject = $subject ? mustache($subject, $data) : null;

        if (!array_key_exists('notifiable', $data)) {
            $data['notifiable'] = $notifiable;
        }

        if ($message instanceof View) {
            /** @var View $view */
            $view = $message;

            foreach ($data as $key => $value) {
                $view = $view->with($key, $value);
            }

            $message = $view->render();
        }

        $message = mustache($message, $data);

        Mail::to($notifiable)
            ->send(new GenericMail($subject, $message));
    }
}
