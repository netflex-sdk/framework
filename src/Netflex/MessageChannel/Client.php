<?php

namespace Netflex\MessageChannel;

use GuzzleHttp\Exception\GuzzleException;
use Netflex\API\Client as API;
use Illuminate\Broadcasting\Channel;

use Illuminate\Support\Traits\Macroable;
use Netflex\API\Exceptions\MissingCredentialsException;

class Client
{
  use Macroable;

  /** @var string */
  public string $channel;

  /** @var API */
  protected API $client;

  /** @var Handler|null */
  protected Handler|null $incomingMessageHandler;

  /**
   * @param string $publicKey
   * @param string $privateKey
   * @param Handler|null $incomingMessageHandler
   * @param string $baseURI
   * @param bool $prefixWithChannel
   * @throws MissingCredentialsException
   * @throws GuzzleException
   */
  public function __construct(
    string $publicKey,
    string $privateKey,
    Handler|null $incomingMessageHandler = null,
    string $baseURI = 'broadcast.netflexapp.com',
    bool $prefixWithChannel = true,
  ) {
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
        $privateKey,
      ],
    ]);

    $this->register();
  }

  /**
   * Retrieves the channel key
   *
   * @return string
   */
  public function key(): string
  {
    return $this->channel;
  }

  /**
   * Registers a Handler
   *
   * @param Handler|null $handler = null
   * @return bool
   * @throws GuzzleException
   */
  public function register(Handler|null $handler = null): bool
  {
    $this->incomingMessageHandler = $handler ?: $this->incomingMessageHandler;

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
   * @param string $topic
   * @return mixed
   * @throws GuzzleException
   */
  public function broadcast(mixed $message, string $topic = 'public'): mixed
  {
    $topic = $topic instanceof Channel ? $topic->name : $topic;
    $response = $this->client->post("broadcast/$topic", ['data' => $message]);

    if (
      !$response->handler
      || (
        $response->handler->endpoint !== $this->incomingMessageHandler->endpoint
      )
    ) {
      $this->register();
    }

    return $response;
  }
}
