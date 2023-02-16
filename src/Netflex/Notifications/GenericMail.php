<?php

namespace Netflex\Notifications;

use Illuminate\Mail\Mailable;

final class GenericMail extends Mailable
{
    public function __construct(?string $subject, string $message)
    {
        $this->html($message);

        if ($subject) {
            $this->subject($subject);
        }
    }

    public function build()
    {
        return $this;
    }
}
