<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

/** @extends ItemCollection<array-key, CartItem> */
class CartItemCollection extends ItemCollection
{
  protected static string $type = CartItem::class;
}
