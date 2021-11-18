<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

class CartItemCollection extends ItemCollection
{
  protected static $type = CartItem::class;
}
