# Netflex MessageChannel

<a href="https://packagist.org/packages/netflex/message-channel"><img src="https://img.shields.io/packagist/v/netflex/message-channel?label=stable" alt="Stable version"></a>
<a href="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml"><img src="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml/badge.svg" alt="Build status"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/log.svg" alt="License: MIT"></a>
<a href="https://github.com/netflex-sdk/sdk/graphs/contributors"><img src="https://img.shields.io/github/contributors/netflex-sdk/sdk.svg?color=green" alt="Contributors"></a>
<a href="https://packagist.org/packages/netflex/message-channel/stats"><img src="https://img.shields.io/packagist/dm/netflex/message-channel" alt="Downloads"></a>

[READ ONLY] Subtree split of the Netflex MessageChannel component (see [netflex/framework](https://github.com/netflex-sdk/framework))

This library lets you send real time messages to connected clients over WebSocket using the Netflex MessageChannel API.

## Installation

Just add this package as a dependency to yout Netflex SDKv2 based project, and it should be ready to use, no additional setup is required.

```bash
composer require netflex/message-channel
```

## Usage

```php
<?php

use MessageChannel;

MessageChannel::broadcast(['status' => 'Hello World']);
```

This will send this message to the MessageChannel API server, which will then relay that message to its connected clients over WebSocket.

## Handling incoming messages

To handle incoming messages, you will have to register a handler.
This can be configured in your apps config `config/message-channel.php`

```php
<?php

return [
  'handler' => [
    'endpoint' => 'https://my-endpoint-to-handle-incoming-messages',
    'method' => 'post' // This is optional, 'post' is the default method
  ]
];
```

## Sending to specific clients/topics

A client can register for a 'topic'. If you use e.g a users ID as the topic, you can then relay messages to this specific topic.

```php
<?php

use MessageChannel;

// Only clients listening for the 'news' topic will receive this message
MessageChannel::broadcast(['status' => 'Hello World'], 'news');
```

## Using with Laravels broadcast system

This package also provides a driver that enables it to work with Laravels broadcast system.

Add the following to your applications `config/broadcasting.php`

```php
<?php

return [
  'default' => 'message-channel',

  'connections' => [

    'message-channel' => [
      'driver' => 'netflex'
    ]
  ]
];
```
