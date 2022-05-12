# PKPass

This utility class helps you build and sign a Apple Wallet Pass.

See https://developer.apple.com/library/archive/documentation/UserExperience/Reference/PassKit_Bundle/Chapters/Introduction.html#//apple_ref/doc/uid/TP40012026-CH0-SW1 for full documentation of the different fields.

## Creating a pass

The PKPass has 5 static factory methods for creating the 5 different pass types supported by Apple Wallet

### Generic pass

Used whenever any of the more specific pass types don't apply.

```php
<?php

use Netflex\Foundation\Wallet\PKPass;

PKPass::generic();
```

### Boarding pass

Used for airplane, bus, train, ferry passes etc.

```php
<?php

use Netflex\Foundation\Wallet\PKPass;

PKPass::boardingPass();
```

### Store card

Used for loyalty cards, gift cards etc.

```php
<?php

use Netflex\Foundation\Wallet\PKPass;

PKPass::storeCard();
```

### Event ticket

Used for concert tickets, sporting tickets etc.

```php
<?php

use Netflex\Foundation\Wallet\PKPass;

PKPass::eventTicket();
```

### Coupon

Used for coupons, discounts etc.

```php
<?php

use Netflex\Foundation\Wallet\PKPass;

PKPass::coupon();
```

### Adding fields

Fields can be added to the pass using the helper methods for adding fields to the various field locations.

The value of a field can be a string or a number.

```php
$pkpass->addHeaderField('title', 'Hello World');
$pkpass->addPrimaryField('title', 'Hello World');
$pkpass->addSecondaryField('title', 'Hello World');
$pkpass->addAuxiliaryField('title', 'Hello World');
$pkpass->addBackField('title', 'Hello World');
```

A label can be added to the field, by passing in a string as the third argument

```php
$pkpass->addHeaderField('title', 'Hello World', 'Title');
```

You can also further customize the field by instead passsing an array as the third argument.

```php
$pkpass->addHeaderField('field-name', 'field-value', [
    'label' => 'Title',                                                     // Label for the field
    'isRelative' => true,                                                   // If value is a date should it be displayed relative to the current date?
    'dateStyle' => PKPass::DATE_STYLE_MEDIUM,                               // Date style to use for displaying dates
    'timeStyle' => PKPass::TIME_STYLE_FULL,                                 // Time style to use for displaying times
    'attributedValue' => '<a href="https://example.com">Click me</a>',      // Override how to field gets rendered
    'changeMessage' => 'Changed',                                           // Message to display when field is changed (only applies when updating a pass)
    'dataDetectorTypes' => PKPASS::DATA_DETECTOR_PHONE_NUMBER,              // Data detector types to use for the field
    'textAlignment' => PKPass::TEXT_ALIGNMENT_CENTER,                       // Text alignment to use for the field
    'currencyCode' => 'NOK',                                                // Currency code to use for the field
    'numberStyle' => PKPass::NUMBER_STYLE_DECIMAL,                          // Number style to use for the field
]);
```

# Designing a pass

The different pass types have different layouts. See the documentation for the specific pass type for more information.
https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Creating.html

### Adding barcode

If no other arguments are passed, the pass will be created with a QR barcode.

```php
$pkpass->barcode('123456789');
```

It is also recommened to add an alternative text label to the pass for accessibility.

```php
$pkpass->barcode('123456789', 'Member ID');
```

This can be customized by passing a barcode format.

```php
$pkpass->barcode('123456789', 'Member ID', PKPASS::FORMAT_QR);
$pkpass->barcode('123456789', 'Member ID', PKPASS::FORMAT_PDF417);
$pkpass->barcode('123456789', 'Member ID', PKPASS::FORMAT_AZTEC);
$pkpass->barcode('123456789', 'Member ID', PKPASS::FORMAT_CODE128);
```

Notice: Even though you can add multiple barcodes by calling barode multiple times, the pass will only display the first barcode added.

### Adding icon, logo, background, strip and footer images to a pass

Images can be added to the pass using the helper methods, or the addImage method of the pass itself.
Supported image types are PNG.

Images are added by passing either a local file path, a URL, or File or MediaUrlResolvable objects.

```php
<?php

$pkpass->addBackground('path/to/background.png');
$pkpass->addIcon('path/to/icon.png');
$pkpass->addLogo('path/to/logo.png');
$pkpass->addStrip('path/to/strip.png');
$pkpass->addThumbnail('path/to/thumnail.png');
$pkpass->addFooter('path/to/footer.png');
```

If you want to add multiple resolutions for the same image, you can use the addFile method instead. Here the name of the image is important. I the file has the correct name, it will be used. Otherwise, you must specify a valid file name manually.

Valid filenames are:

* icon.png
* icon@2x.png
* logo.png
* background.png
* thumbnail.png
* footer.png
* strip.png

```php
<?php

$pkass->addImage('path/to/some/file.png', 'icon@2x.png');
```

### Customizing colors

Colors can be customized by passing a CSS rgb or rgba color

```php
$pkpass->foregroundColor('rgb(0,0,0)');
$pkpass->labelColor('rgb(0,0,0)');
$pkpass->stripColor('rgb(127,127,127)');
$pkpass->backgroundColor('rgb(255,255,255)');
$pkpass->backgroundColor('rgb(255,255,255)');
```
