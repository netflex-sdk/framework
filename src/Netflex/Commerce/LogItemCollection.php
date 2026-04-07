<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

/** @extends ItemCollection<array-key, LogItem> */
class LogItemCollection extends ItemCollection
{
  protected static string $type = LogItem::class;
}
