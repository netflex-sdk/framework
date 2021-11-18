<?php

use Netflex\MessageChannel\Facades\MessageChannel;

if (!function_exists('message_channel_key')) {
  /**
   * Retrieves the applications message channel key
   * @return string
   */
  function message_channel_key()
  {
    return MessageChannel::key();
  }
}
