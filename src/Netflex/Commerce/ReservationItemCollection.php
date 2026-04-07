<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

/** @extends ItemCollection<array-key, ReservationItem> */
class ReservationItemCollection extends ItemCollection
{
  protected static string $type = ReservationItem::class;
}
