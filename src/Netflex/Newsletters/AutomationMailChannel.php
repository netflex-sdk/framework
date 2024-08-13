<?php
namespace Netflex\Newsletters;

use Illuminate\Notifications\Notification;
use Netflex\API\Facades\API;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class AutomationMailChannel
{
  public function send(object $notifiable, Notification $notification)
  {
    /** @var \Netflex\Newsletters\AutomationMailMessage|\Netflex\Newsletters\Contracts\AutomationMailNotification $message */
    $message = $notification->toAutomationMail($notifiable);

    $body = $this->inlineCss((string)$message);

    API::post('relations/notifications', array_filter([
      'subject' => $message->getSubject(),
      'to' => [$notifiable->mail],
      'from' => $message->getFrom() ?? variable('mail_sender_mail') ?: null,
      'reply_to' => $message->getReplyTo(),
      'body' => base64_encode($body),
      'newsletter_id' => $message->getNewsletterId(),
      'use_blank_template' => true,
      //'attachments' => $attachments
    ]));
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
