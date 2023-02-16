<?php

namespace Netflex\Notifications;

final class GenericSmsNotifiation
{
    protected string $message;
    protected ?string $from;
    protected array $data;

    public function __construct(string $message, ?string $from = null, array $data = [])
    {
        $this->message = $message;
        $this->from = $from;
        $this->data = $data;
    }

    public function from()
    {
        if ($this->from) {
            return $this->from;
        }

        return variable('sms_from');
    }

    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms($notifiable)
    {
        return mustache($this->message, $this->data);
    }
}
