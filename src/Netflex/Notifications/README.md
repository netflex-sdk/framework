# Netflex Notifications

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
