# Netflex Notifications

<a href="https://packagist.org/packages/netflex/notifications"><img src="https://img.shields.io/packagist/v/netflex/notifications?label=stable" alt="Stable version"></a>
<a href="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml"><img src="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml/badge.svg" alt="Build status"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/log.svg" alt="License: MIT"></a>
<a href="https://github.com/netflex-sdk/sdk/graphs/contributors"><img src="https://img.shields.io/github/contributors/netflex-sdk/sdk.svg?color=green" alt="Contributors"></a>
<a href="https://packagist.org/packages/netflex/notifications/stats"><img src="https://img.shields.io/packagist/dm/netflex/notifications" alt="Downloads"></a>

[READ ONLY] Subtree split of the Netflex Notification component (see [netflex/framework](https://github.con/netflex-sdk/framework))
## Installation

```bash
composer require netflex/notifications
```

## Setup

In `config/mail.php`:

```php
[
  'driver' => env('MAIL_DRIVER', 'netflex')
]
```

## Usage

You can use this driver to send any [Mailables](https://laravel.com/docs/7.x/mail#writing-mailables).
It also integrates with Laravels [Notification](https://laravel.com/docs/7.x/notifications) system (and adds a 'sms' channel, just implement toSMS on your notification).

```php
<?php
use App\Mail\OrderConfirmed;

Mail::to($request->user())->send(new OrderConfirmed($order));
```

It also supports the legacy Netflex mail templates:

```php
<?php

use Netflex\Notifications\Notification;

Mail::to($request->user())->send(
    Notification::resolve('order_confirmed', [
        'firstname' => $order->customer_firstname
    ])
);
```
