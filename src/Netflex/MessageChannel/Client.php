<?php

namespace Netflex\MessageChannel;

use Netflex\API\Client as API;
use Illuminate\Broadcasting\Channel;

use Illuminate\Support\Traits\Macroable;
use Netflex\MessageChannel\Facades\MessageChannel;

class Client
{
  use Macroable;

  /** @var string */
  public $channel;

  /** @var API */
  protected $client;

  /** @var IncomingMessageHandler|null */
  protected $incomingMessageHandler;

  /**
   * @param string $publicKey
   * @param string $privateKey
   * @param IncomingMessageHandler|null $incomingMessageHandler
   */
  public function __construct($publicKey, $privateKey, $incomingMessageHandler = null, $baseURI = 'broadcast.netflexapp.com', $prefixWithChannel = true)
  {
    $this->channel = md5_to_uuid(md5($publicKey));
    $this->incomingMessageHandler = $incomingMessageHandler;

    $channelKey = $this->key();

    $matches = [];
    $protocol = 'https://';

    if ($prefixWithChannel) {
      if (preg_match('/(https?:\/\/)(.+)/', $baseURI, $matches)) {
        $protocol = $matches[1];
        $baseURI = $matches[2];
      }

      $baseURI = $protocol . implode('.', [$channelKey, $baseURI]);
    }

    $this->client = new API([
      'base_uri' => $baseURI,
      'auth' => [
        $publicKey,
        $privateKey
      ]
    ]);

    $this->register();
  }

  /**
   * Retrieves the channel key
   * 
   * @return string
   */
  public function key()
  {
    return $this->channel;
  }

  /**
   * Registers a Handler
   * 
   * @param Handler $handler = null
   */
  public function register(Handler $handler = null)
  {
    $this->incomingMessageHandler = $handler ? $handler : $this->incomingMessageHandler;

    if ($this->incomingMessageHandler) {
      $this->client->post('register', $this->incomingMessageHandler);
      return true;
    }

    return false;
  }

  /**
   * Broadcasts the message to the given topic
   * 
   * @param mixed $message
   * @param Channel|string $topic
   * @return mixed
   */
  public function broadcast($message, $topic = 'public')
  {
    $topic = $topic instanceof Channel ? $topic->name : $topic;
    $response = $this->client->post("broadcast/$topic", ['data' => $message]);

    if (!$response->handler || $response->handler->endpoint !== $this->incomingMessageHandler->endpoint) {
      $this->register();
    }

    return $response;
  }
}
