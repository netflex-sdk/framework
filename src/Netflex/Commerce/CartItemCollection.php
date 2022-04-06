<?php

namespace Netflex\Commerce;

use Netflex\Support\ItemCollection;

class CartItemCollection extends ItemCollection
{
    protected static $type = CartItem::class;

    /**
     *
     * Casts the cart item to whatever class is stored in the class property.
     *
     * The class put here MUST extend from Netflex\Commerce\CartItem
     *
     */
    protected function generateInstance($item)
    {
        try {
            if (is_array($item['properties']) && ($class = $item['properties']['_class'] ?? null) != null) {
                return $class::factory($item);
            }
        } catch (\Exception $e) {
            return parent::generateInstance($item);
        }
        return parent::generateInstance($item);
    }
}
