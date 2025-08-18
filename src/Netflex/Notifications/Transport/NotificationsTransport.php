<?php

namespace Netflex\Notifications\Transport;

use Netflex\API\Facades\API;
use Netflex\Foundation\Variable;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

class NotificationsTransport extends AbstractTransport
{
  protected function doSend(SentMessage $message): void
  {
    $email = MessageConverter::toEmail($message->getOriginalMessage());

    $replyTo = null;
    $replyToAddresses = $email->getReplyTo();
    if (!empty($replyToAddresses)) {
      $addr = $replyToAddresses[0];
      $replyTo = $addr->getName()
        ? sprintf('%s <%s>', $addr->getName(), $addr->getAddress())
        : $addr->getAddress();
    }

    $attachments = [];
    foreach ($email->getAttachments() as $part) {
      if (!$part instanceof DataPart) {
        continue;
      }

      $filename = $part->getFilename() ?? 'attachment';
      $contentType = $part->getMediaType() . '/' . $part->getMediaSubtype();

      $binary = $part->getBody();
      $link = 'data://' . $contentType . ';base64,' . base64_encode($binary);

      $attachments[] = [
        'filename' => $filename,
        'link' => $link,
      ];
    }

    $body = $email->getHtmlBody() ?? $email->getTextBody() ?? '';

    $payload = array_filter([
      'subject' => $email->getSubject(),
      'to' => $this->mapTo($email->getTo()),
      'from' => $this->mapFrom($email->getFrom()),
      'reply_to' => $replyTo,
      'body' => base64_encode($body),
      'use_blank_template' => true,
      'attachments' => $attachments,
    ], static fn ($v) => $v !== null && $v !== []);

    $response = API::post('relations/notifications', $payload);

    $email->getHeaders()->addTextHeader('X-Notification-ID', (string) data_get($response, 'notification_id'));
  }

  public function __toString(): string
  {
    return 'netflex-notifications';
  }

  /** @param Address[] $addresses */
  protected function mapTo(array $addresses): array
  {
    return collect($addresses)
      ->map(fn (Address $a) => ['mail' => $a->getAddress(), 'name' => $a->getName()])
      ->values()
      ->all();
  }

  /** @param Address[] $addresses */
  protected function mapFrom(array $addresses): array
  {
    /** @var Address|null $first */
    $first = $addresses[0] ?? null;

    return [
      'mail' => $first?->getAddress() ?? (string) Variable::get('mail_sender_mail'),
      'name' => $first?->getName() ?? (string) Variable::get('mail_sender_name'),
    ];
  }
}
