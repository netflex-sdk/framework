<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

/** @extends ItemCollection<array-key, DiscountItem> */
class DiscountItemCollection extends ItemCollection
{
  protected static string $type = DiscountItem::class;
}
