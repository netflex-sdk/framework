# Netflex Http

[READ ONLY] Subtree split of the Netflex Http component (see [netflex/framework](https://github.con/netflex-sdk/framework))

This library provides a standalone Http client.

## Installation

```bash
composer require netflex/http
```

## Usage

```php
<?php

use Netflex\Http\Client;

$client = new Client();

$post = $client->get('https://jsonplaceholder.typicode.com/posts/1');

echo $post->title;
```

The default for the client is to automatically parse the content based on it's content type.

If the content type is application/json, it will be parsed as an object.
If you need the response as a associative array instead, all the http methods on the client takes an optional last boolean argument that enables this.

```php
$post = $client->get('https://jsonplaceholder.typicode.com/posts/1', true);

echo $post['title'];
```

## Laravel service provider and facade

If installed through Laravel, you can use the Facade. The service provider will auto register.

```php
<?php

use Netflex\Http\Facades\Http;

$post = Http::get('https://jsonplaceholder.typicode.com/posts/1');

echo $post->title;
```

You can optionally use the alias:

```php
<?php

use Http;

$post = Http::get('https://jsonplaceholder.typicode.com/posts/1');

echo $post->title;
```