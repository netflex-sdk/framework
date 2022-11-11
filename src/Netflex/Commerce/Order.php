<?php

namespace Netflex\Commerce;

class Order extends AbstractOrder
{
  /**
   * User exposed observable events.
   *
   * These are extra user-defined events observers may subscribe to.
   *
   * @var array
   */
  protected $observables = [
    'registered',
    'locked',
    'checkout'
  ];
}
