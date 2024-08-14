<?php

namespace Netflex\Newsletters\Facades;

class AutomationMails
{

  /** @noinspection PhpUndefinedMethodInspection */
  public static function registerAutomationMail(int $mailId, string $notification_class)
  {
    try {
      $errorReason = "The notification class must have static method called [automationPreviewParameters] which returns a key/value array that maps all variables required by the class constructor";
      $rms = new \ReflectionMethod($notification_class, 'automationPreviewParameters');
      if (!$rms->isStatic()) {
        throw static::makeException($errorReason);
      }
    } catch (\ReflectionException $exception) {
      throw static::makeException($errorReason, $exception);
    }

    try {
      $errorReason = "The notification class must have a static method called [automationPreviewNotifiable] which takes the notification as an arguments and returns the preview notifiable";
      $rms = new \ReflectionMethod($notification_class, 'automationPreviewNotifiable');
      if (!$rms->isStatic()) {
        throw static::makeException($errorReason);
      }
    } catch (\ReflectionException $exception) {
      throw static::makeException($errorReason, $exception);
    }

    app()
      ->bind("automation-mail.$mailId", fn() => app($notification_class, $notification_class::automationPreviewParameters()));
  }


  private static function makeException(string $text, ?\Exception $exception = null)
  {
    return new \Exception(
      $exception ? $exception->getCode() : null,
      $exception
    );
  }
}
