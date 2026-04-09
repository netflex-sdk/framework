<?php

namespace Netflex\Commerce;

use Netflex\Support\ReactiveObject;
use Netflex\Commerce\Traits\API\LogItemAPI;

/**
 * @property int $id
 * @property string $created
 * @property string $updated
 * @property string $type
 * @property int $userid
 * @property string $msg
 * @property string $receiver_mail
 * @property string $mail_sent_time
 * @property string $confirm_read
 */
class LogItem extends ReactiveObject
{
  use LogItemAPI;

  protected array $readOnlyAttributes = [
    'id',
    'updated'
  ];

  protected array $defaults = [
    'type' => null,
    'msg' => null
  ];

  public function getOrderIdAttribute($value)
  {
    return (int) $value;
  }
}
