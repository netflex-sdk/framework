<?php

namespace Netflex\Newsletters;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Netflex\API\Facades\API;
use Netflex\Customers\Customer;
use Netflex\Newsletters\Contracts\IsConsentConstrained;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class AutomationMailChannel
{
  public function send(object $notifiable, Notification $notification)
  {
    /** @var \Netflex\Newsletters\AutomationMailMessage|\Netflex\Newsletters\Contracts\AutomationMailNotification $message */
    $message = $notification->toAutomationMail($notifiable);

    if ($notification instanceof IsConsentConstrained) {
      $acceptedConsents = collect($notification->requiredConsents());
      if ($acceptedConsents->count() > 0 && $acceptedConsents->filter(fn($id) => $notifiable->hasConsent($id))->count() === 0) {
        return;
      }
    }

    $body = $this->inlineCss((string)$message);

    $to = [[
      'mail' => $notifiable->mail,
      'name' => $notifiable->name ?: $notifiable->mail
    ]];

    $id = API::post('relations/notifications', array_filter([
      'subject' => $message->getSubject(),
      'to' => $to,
      'from' => $message->getFrom(),
      //'reply_to' => $replyTo,
      'body' => base64_encode($body),
      'use_blank_template' => true,
      'newsletter_id' => $message->getNewsletter()->id,
      'customer_id' => $notifiable instanceof Customer ? $notifiable->id : null,
      //'attachments' => $attachments
    ]));

    $type = get_class($notification);
    $id = data_get($id, 'notification_id');
    return $id;
  }

  private function inlineCss(string $content, $tags = ['src', 'href']): string
  {
    $replaceWith = "wer90erjgfierjgi43j5829uy45293u428973yreguhrueirjghui9efjrtu89eiodkfjghrui9ekopwdfiu98gri0tk3";
    $replacements = array_map(fn($str) => "$replaceWith$str", $tags);
    $convertedContent = str_replace($tags, $replacements, $content);
    $convertedContent = (new CssToInlineStyles())->convert($convertedContent);
    return str_replace($replacements, $tags, $convertedContent);
  }
}
