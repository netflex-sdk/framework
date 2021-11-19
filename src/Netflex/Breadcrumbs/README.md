# Netflex Breadcrumbs

<a href="https://packagist.org/packages/netflex/breadcrumbs"><img src="https://img.shields.io/packagist/v/netflex/breadcrumbs?label=stable" alt="Stable version"></a>
<a href="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml"><img src="https://github.com/netflex-sdk/framework/actions/workflows/split_monorepo.yaml/badge.svg" alt="Build status"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/log.svg" alt="License: MIT"></a>
<a href="https://github.com/netflex-sdk/sdk/graphs/contributors"><img src="https://img.shields.io/github/contributors/netflex-sdk/sdk.svg?color=green" alt="Contributors"></a>
<a href="https://packagist.org/packages/netflex/breadcrumbs/stats"><img src="https://img.shields.io/packagist/dm/netflex/breadcrumbs" alt="Downloads"></a>

[READ ONLY] Subtree split of the Netflex Breadcrumbs component (see [netflex/framework](https://github.com/netflex-sdk/framework))

Utilitiy class for creating breadcrumb navigations in Netflex Pages.

## Table of Contents

  * [Installation](#installation)
  * [Usage](#usage)
  * [Options](#options)
  * [Example usage in Blade components](#example-usage-in-blade-components)

## Installation

```bash
composer require netflex/breadcrumbs
```

## Usage
```php
use Netflex\Breadcrumbs\Breadcrumb;
use Netflex\Pages\Page;

// Create breadcrumb based on current page
$page = Page::current();
$options = [];
$breadcrumb = new Breadcrumb($page, $options);

// Get breadcrumb data
$breadcrumb = $breadcrumb->get();
```
 
The returned breadcrumb data will be formatted like this:
```php
Illuminate\Support\Collection Object ( 
  [items:protected] => Array ( 
    [0] => Array ( 
      [label] => 'Home'
      [originalLabel] => 'Home'
      [path] => '/'
      [id] => 10000 
      [type] => 'page'
      [published] => true 
      [current] => false
    ) 
    [1] => Array ( 
      [label] => 'Example' 
      [originalLabel] => 'Example' 
      [path] => '/example' 
      [id] => 10027 
      [type] => 'page' 
      [published] => true 
      [current] => true
    )
  )
)
```

## Options
Options can be passed as an array when you instantiate the class.

| Option | Type | Default value | Description |
|---|---|---|---|
| `hideCurrentPage` | `boolean` | `false` | If set to `true` the current page will be omitted from the breadcrumb |
| `hideRootPage` | `boolean` | `false` | If set to `true` the root page will be omitted from the breadcrumb |
| `overrideRootPageLabel` | `string\|null` | `null` | If provided with a string, it will override the label of the root page |
| `overrideRootPagePath` | `string\|null` | `null` | If provided with a string, it will override the path of the root page |
| `inferCurrentPage` | `boolean` | `true` | If set to `false` the current page will not be marked as current in the breadcrumb. Useful when appending items manually. |

## Example usage in Blade components
```html
<nav class="Breadcrumb" aria-label="Breadcrumb">
  <ol class="Breadcrumb__list">
    @foreach ($breadcrumb as $item)
      @if ($item['current'])
        <li class="Breadcrumb__item Breadcrumb__item--active">
          <a class="Breadcrumb__link" href="{{ $item['path'] }}" aria-current="page">
            {{ $item['label'] }}
          </a>
        </li>
      @else
        <li class="Breadcrumb__item">
          <a class="Breadcrumb__link" href="{{ $item['path'] }}">
            {{ $item['label'] }}
          </a>
        </li>
      @endif
    @endforeach
  </ol>
</nav>
```