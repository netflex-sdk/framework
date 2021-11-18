# Netflex Renderer

This package provides a high-level builder interface for generating PDF's, images, and server side rendering of HTML.

Full API reference is [available here](https://netflex-sdk.github.io/docs/api/Netflex/Render.html).

<a href="https://packagist.org/packages/netflex/renderer"><img src="https://img.shields.io/packagist/v/netflex/renderer?label=stable" alt="Stable version"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/github/license/netflex-sdk/renderer.svg" alt="License: MIT"></a>
<a href="https://packagist.org/packages/netflex/renderer/stats"><img src="https://img.shields.io/packagist/dm/netflex/renderer" alt="Downloads"></a>

## Table of contents

- [Installation](#installation)
- [Usage](#usage)
  * [Generating PDF/JPG/PNG/HTML](#generating-pdfjpgpnghtml)
    + [From raw HTML](#from-raw-html)
    + [By rendering a View](#by-rendering-a-view)
    + [By rendering a named Route](#by-rendering-a-named-route)
    + [By rendering a URL](#by-rendering-a-url)
  * [Setting pixel ratio](#setting-pixel-ratio)
  * [Specifying a timeout](#specifying-a-timeout)
  * [Waiting for the document to load](#waiting-for-the-document-to-load)
  * [Retrieving the rendered content](#retrieving-the-rendered-content)
- [PDF](#pdf)
  * [Specify page ranges](#specify-page-ranges)
  * [Print background](#print-background)
  * [Margings](#margings)
  * [Paper format](#paper-format)
  * [Document scaling](#document-scaling)
  * [Explicit size](#explicit-size)
  * [Landscape mode](#landscape-mode)
  * [Set size from CSS](#set-size-from-css)
  * [Setting custom header and footer](#setting-custom-header-and-footer)
    + [Outputting page numbers etc.](#outputting-page-numbers-etc)
    + [Page break utilities](#page-break-utilities)
  * [Tags and metadata](#tags-and-metadata)
- [Images](#images)
  * [Setting viewport size](#setting-viewport-size)
  * [Capturing a specific element using a CSS selector](#capturing-a-specific-element-use-a-css-selector)
  * [Clip](#clip)
  * [Full page](#full-page)
  * [JPG](#jpg)
    + [Quality](#quality)
  * [PNG](#png)
    + [Transparent](#transparent)
- [HTML](#html)
  * [Server Side Rendering (SSR)](#server-side-rendering-ssr)
- [MJML](#mjml)
  * [Usage in Mailable](#usage-in-mailable)
- [View macros](#view-macros)


## Installation

```bash
composer require netflex/renderer
```

## Usage

### Generating PDF/JPG/PNG/HTML

These methods also applies to the `HTML`, `PNG`, and `JPG` classes.

#### From raw HTML
```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::from('<h1>Hello, World!</h1>');
```

#### By rendering a View
```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);
```

#### By rendering a named Route
```php
<?php

use Netflex\Render\PDF;
use App\Models\Product;

$pdf = PDF::route('products.show', ['product' => Product::first()]);
```

#### By rendering a URL
```php
<?php

use Netflex\Render\PDF;

// Internal URL (must be publicly reachable)
$pdf = PDF::url('/test');

// External URL
$pdf = PDF::url('https://www.apility.no');
```

### Setting pixel ratio

These methods also applies to the `PNG`, and `JPG` classes.

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::from('<h1>Hello, World!</h1>');

$pdf->devicePixelRatio(2.0);
```

### Specifying a timeout

Sometimes the document can take a while to load. You can specify how long you want to wait until the request is considered timed out.

These methods also applies to the `HTML`, `PNG`, and `JPG` classes.

```php
use Netflex\Render\PDF;

$pdf = PDF::from('<h1>Hello, World!</h1>');

// Only allow the request to load for 5 seconds, then time out.
$pdf->timeout(5000);
```

### Waiting for the document to load

If your document is client side rendered with JavaScript, you sometimes have to wait a bit before the document is captured. Otherwise you risk getting a blank or partial blank result.

These methods also applies to the `HTML`, `PNG`, and `JPG` classes.

```php
use Netflex\Render\PDF;

$pdf = PDF::from('<h1>Hello, World!</h1>');

// Waits until the whole page has loaded, including all dependent resources such as stylesheets and images.
$pdf->waitUntilLoaded();

// Waits until fired the page DOM has been loaded, without waiting for resources to finish loading.
$pdf->waitUntilDOMContentLoaded();

// Waits until there has not been any network requests for at least 500ms
$pdf->waitUntiNetworkIdle()

// Waits until there has not been more than 2 network requests for at least 500ms
$pdf->waitUntiNetworkSettled();
```

### Retrieving the rendered content

These methods also applies to the `HTML`, `PNG`, and `JPG` classes.

```php
<?php

use Netflex\Render\PDF;

// As a Laravel response:
$response = $pdf->toResponse();

// As a file handle
$file = $pdf->stream();

// As a link
$url = $file->link();

// As a string
$str = $file->blob();
```

All the renderers implements Laravel's `Responsable` interface. This means that you can return them directly from you Route or Controller, and they will automatically be converted to a Response.

```php
<?php

use Netflex\Render\PDF;

Route::get('example.pdf', function () {
    return PDF::from('<h1>Hello, World!</h1>');
});
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Netflex\Render\PDF;

class ExampleController extends Controller
{
    public function show (Product $product)
    {
        return PDF::view('product', ['product' => $product]);
    }
}
```

## PDF

### Specify page ranges

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->pageRanges(1, 3, [5, 10], 12, /* ... */);
```

### Print background

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->printBackground();
```

### Margings

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->marginTop('1cm');
$pdf->marginRight('100mm');
$pdf->marginBottom('128px');
$pdf->marginLeft(200);

// ... or

$pdf->marginTop(1, PDF::UNIT_CM);
$pdf->marginRight(100, PDF::UNIT_MM);
$pdf->marginBottom(128, PDF::UNIT_PX);
$pdf->marginLeft(200); // Let the backend decide the unit

// Or specify them like you would in CSS
$pdf->margin('1cm'); // All margings set to 1cm
$pdf->margin('1cm', '2cm'); // Top and bottom set to 1cm, Left and right to 2cm
```

### Paper format

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

// Metric sizes
$pdf->format(PDF::FORMAT_A0);
$pdf->format(PDF::FORMAT_A1);
$pdf->format(PDF::FORMAT_A2);
$pdf->format(PDF::FORMAT_A3);
$pdf->format(PDF::FORMAT_A4); // <-- Default
$pdf->format(PDF::FORMAT_A5);
$pdf->format(PDF::FORMAT_A6);

// US. sizes
$pdf->format(PDF::FORMAT_LETTER);
$pdf->format(PDF::FORMAT_LEGAL);
$pdf->format(PDF::FORMAT_TABLOID;
$pdf->format(PDF::FORMAT_LEDGER);
```

### Document scaling

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->scale(1.5); // Scale factor between 0.1 and 2.0
```

### Explicit size

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->width('100cm');
$pdf->height('200cm');
```

### Landscape mode

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->format(PDF::FORMAT_A3);
$pdf->landscape();
```

### Set size from CSS

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

$pdf->preferCSSPageSize();
```

### Setting custom header and footer

You can override the default header and footer by providing your own view.

Do note that if you specify a custom template for the header, and don't provide a custom template for the footer, the default footer will show. This also applies the other way around. This is a limitation of the render backend.

```php
<?php

use Netflex\Render\PDF;

$pdf = PDF::view('templates.example', ['foo' => 'bar']);

// If you just would like to enable the default header and footer
// Not needed if you specify a custom header or footer.
$pdf->displayHeaderFooter();

$pdf->headerTemplate('blocks.pdf.header', ['hello' => 'world']);
$pdf->footerTemplate('blocks.pdf.header', ['hello' => 'world']);

// Or from raw markup
$pdf->headerTemplateFrom('<div><span class="date"></span></div>');
$pdf->footerTemplateFrom('<div><span class="date"></span></div>');
```

#### Outputting page numbers etc.

If you do specify a custom header or footer view, you can use the following Blade directives.

```html
<div class="example-footer">
    <div>@pdf_date</div>
    <div>@pdf_title</div>
    <div>@pdf_url</div>
    <div>@pdf_page_number</div>
    <div>@pdf_total_pages</div>
</div>
```

#### Page break utilities

```html
<div>@pdf_page_break</div>
<div>@pdf_page_break_before</div>
<div>@pdf_page_break_before_avoid</div>
<div>@pdf_page_break_after</div>
<div>@pdf_page_break_after_avoid</div>
```

### Tags and metadata

You can set PDF tags and metadata.

```php
<?php

use Carbon\Carbon;
use Netflex\Render\PDF;

$pdf = PDF::url('https://www.google.com');

$pdf->author('John Doe');
$pdf->title('Hello World!');
$pdf->keywords(['foo', 'bar', 'baz']);
$pdf->description('Lorem ipsum dolor sit amet, consectetur adipiscing elit');
$pdf->creator('Example Company Inc.');

// You can also override the creation and modified dates
$now = Carbon::now();

$pdf->created($now);
$pdf->modified($now);
```

## Images

### Setting viewport size

The default viewport is `1920x1080` at `1x` devicePixelRatio.

```php
<?php

use Netflex\Render\PNG;

$png = PNG::view('templates.example', ['foo' => 'bar']);

// Viewport size 2560x1440 at 2x devicePixelRatio
$png->width(2560);
$png->height(1440)
$png->devicePixelRatio(2.0);
```

These options are shared between JPG and PNG

### Capturing a specific element use a CSS selector

Notice: Only the first matched element will be captured.

```php
<?php

use Netflex\Render\PNG;

$png = PNG::view('templates.example', ['foo' => 'bar']);

$png = $png->selector('div.card');
$png = $png->selector('#logo');
$png = $png->selector('span');
```

### Clip

Notice: Clipping is always relative to the full document, even when using a selector to target an element. This is a backend limitation, and could change in the future.

```php
<?php

use Netflex\Render\PNG;

$png = PNG::view('templates.example', ['foo' => 'bar']);

// Extract a 256x256 image starting from x:10, y:10 offsets from the document top
$png = $png->clip(10, 10, 256, 256);
```

### Full page

If you want to capture the entire page, including content not visible in the viewport.

```php
<?php

use Netflex\Render\PNG;

$png = PNG::view('templates.example', ['foo' => 'bar']);

$png = $png->fullPage();
```


### JPG

#### Quality

```php
<?php

use Netflex\Render\JPG;

$jpg = JPG::view('templates.example', ['foo' => 'bar']);

// Best quality
$jpg = $jpg->quality(100);

// Worst quality
$jpg = $jpg->quality(0);
```

### PNG

#### Transparent

Preserves background opacities.

```php
<?php

use Netflex\Render\PNG;

$png = PNG::view('templates.example', ['foo' => 'bar']);

$png->transparent();
```

## HTML

The HTML renderer can be used for getting rendered content as HTML.
This can be very useful if parts of your view/url is rendered client side with JavaScript.

```php
<?php

use Netflex\Render\HTML;

$html = HTML::view('templates.example', ['foo' => 'bar']);

$content = $html->blob();
```

### Server Side Rendering (SSR)

This package provides a middleware that you can use to server side render your content.

`Netflex\Render\Http\Middleware\SSR`

Just register that in you `app/Http/Kernel.php` file

```php
<?php

namespace App\Http;

use Netflex\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'ssr' => \Netflex\Render\Http\Middleware\SSR::class,
    ];
```

And now you can use it in a route:

```php
<?php

Route::group(['middleware' => 'ssr'], function () {
    Route::get('/example', 'ExampleController@show');
});
```

## MJML

The MJML rendered is used to transform MJML to HTML.
[MJML](https://mjml.io/) is a framework for generating responsive email markup.

The MJML renderer isn't very usefull standalone, but provides a few helpers that can hook into [Mailable](https://laravel.com/docs/8.x/mail).

Example:

```php
<?php

use Netflex\Render\MJML;

$html = MJML::from('<mjml><mj-body><mj-text>Hello World</mj-text></mj-body</mml>')
  ->blobl();
```

### Usage in Mailable

Instead of using a .blade.php file, write your view as a .mjml file.

In your Mailable class, add the trait `Netflex\Render\Mail\MJML`.

Example:

```html
<!-- resources/views/mail/example.mjml -->
<mjml>
    <mj-body>
        <mj-section>
            <mj-column width="100%">
              <mj-text>
                {{ $message }}
                </mj-text>
            </mj-column>
        </mj-section>
    </mj-body>
</mjml>
```

```php
<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Netflex\Render\Mail\MJML;

class ExampleMail extends Mailable
{
  use MJML;

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->mjml('mail.example', ['message' => 'Hello World'])
            ->subject('Rendered with MJML!');
    }
}
```

The resulting rendered markup will be cached until the source .mjml file changes. But variables passed to the view will still be injected dynamically.

## View macros

This package registers a few macros to the `View` class for your convenience.

```php
<?php

use Illuminate\Support\Facades\View;

View::make('example')->renderPDF();
View::make('example')->renderJPG();
View::make('example')->renderPNG();
View::make('example')->renderHTML();

// You can also chain all the other options
View::make('example')->renderPDF()
    ->printBackground(); // ...etc
```

---

Licensed under the [MIT license](LICENSE).

Copyright [Apility AS](https://www.apility.no/) &copy; 2021
