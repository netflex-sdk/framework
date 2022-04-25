# Netflex Actions

<a href="https://packagist.org/packages/netflex/commerce"><img src="https://img.shields.io/packagist/v/netflex/commerce?label=stable" alt="Stable version"></a>
<a href="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml"><img src="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml/badge.svg" alt="Build status"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/log.svg" alt="License: MIT"></a>
<a href="https://github.com/netflex-sdk/sdk/graphs/contributors"><img src="https://img.shields.io/github/contributors/netflex-sdk/sdk.svg?color=green" alt="Contributors"></a>
<a href="https://packagist.org/packages/netflex/commerce/stats"><img src="https://img.shields.io/packagist/dm/netflex/commerce" alt="Downloads"></a>

[READ ONLY] Subtree split of the Netflex Commerce component (see [netflex/framework](https://github.com/netflex-sdk/framework))

The Netflex Commerce library is for working with the commerce endpoints in the Netflex API.

<p>
<a href="https://packagist.org/packages/netflex/commerce/stats"><img src="https://img.shields.io/packagist/dm/netflex/commerce" alt="Downloads"></a>
<a href="https://packagist.org/packages/netflex/commerce"><img src="https://img.shields.io/packagist/v/netflex/commerce?label=stable" alt="Stable version"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/sdk.svg" alt="License: MIT"></a>
</p>

## Installation

```bash
composer require netflex/actions
```

## Getting started 

### 1. Run the install script
```shell
$ php artisan actions:install
```

### 2. Change the appropriate settings in netflex
A new **netflex_actions** setting shall have appeared in Netflex. 
Adjust this depending on your enviroment and functionality.

### 3. Register the service provider
A new file named `ActionsServiceProvider.php` has appeared in your providers
folder. Load this file by adding it to the `config/app.php` under the `providers`section.

### 4. Run the appropriate scaffold function
```shell
$ php artisan actions:scaffold:orders:refunds
```

New files will appear in your `app/Actions/` folder. The service provider will be automatically updated
unless you messed with the file.

### 5. Get developing.
You can now get developing.


## Forms

Some modules such as the refund module uses a limited form builder that resides in Netflexapp.
In order to display forms.

All of these form fields can be created with a static `::create()` method.
For example 
```php
<?php
$input = TextInput::create('name', 'What is your name');
```

Some fields have different properties that can be set.
This are set using methods prefixed with `with` such as `withValue`

```php
$input = Select::create('pastry', 'Kake eller boller')
    ->withOptions([
           'kake' => 'Kake',
           'bolle' => 'Bolle'
     ]);
```

### Subtitles and predetermined values
All functions has a `withDescription` and `withValue` function that sets the default
value or subtitle for the field.

**While some of these modules are technially not form fields and are not interactable.
They serve as useful UI elements and are constructed similarilly. Alias and description does not matter for these components**

### Alert
Creates a bootstrap alert

Has a `withLevel` and `withMessage` extra message. Alias is ignored for this component

```php
$input = Alert::create('', 'oops')
    ->withLevel('danger')
    ->withMessage('An error has occurred');
```


### FreeText
FreeText field where you can write whatever. Useful for headers and subheaders, as HTML is allowed.
Has a `withMessage` extra message. Alias is ignored for this component

```php
$input = Alert::create('', 'oops')
    ->withMessage('<h1>User info</h1>');
```

### Integer
Creates an integer field.

Has a `withMinValue` and a `withMaxValue` or a `withRange` if you want to set both in one go.

```php
Integer::create('quantity', 'How many redbulls do you want?')
->withRange(100, 1000);
```

### Select
Creates a select field.

Has a 'withOptions' function.

```php
Select::create('pastry', 'Kake eller boller')
    ->withOptions([
           'kake' => 'Kake',
           'bolle' => 'Bolle'
     ]);
```

### TextField
Creates a simple text input.
Has no special options

```php
TextField::create('comment', 'Har du noen kommentar?');
```

