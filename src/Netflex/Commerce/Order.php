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
    'checkout',
    'paying',
    'paid',
  ];

  /**
   * Register a model event with the dispatcher.
   *
   * @param  string  $event
   * @param  \Illuminate\Events\QueuedClosure|\Closure|string  $callback
   * @return void
   */
  protected static function registerModelEvent($event, $callback)
  {
    if (isset(static::$dispatcher)) {
      static::$dispatcher->listen("netflex.commerce.order.{$event}", $callback);
    }
  }

  /**
   * Fire the given event for the model.
   *
   * @param  string  $event
   * @param  bool  $halt
   * @return mixed
   */
  protected function fireModelEvent($event, $halt = true)
  {
    if (!isset(static::$dispatcher)) {
      return true;
    }

    // First, we will get the proper method to call on the event dispatcher, and then we
    // will attempt to fire a custom, object based event for the given event. If that
    // returns a result we can return that result, or we'll call the string events.
    $method = $halt ? 'until' : 'dispatch';

    $result = $this->filterModelEventResults(
      $this->fireCustomModelEvent($event, $method)
    );

    if ($result === false) {
      return false;
    }

    return !empty($result) ? $result : static::$dispatcher->{$method}(
      "netflex.commerce.order.{$event}",
      $this
    );
  }

  /**
   * Remove all of the event listeners for the model.
   *
   * @return void
   */
  public static function flushEventListeners()
  {
    if (!isset(static::$dispatcher)) {
      return;
    }

    $instance = new static;

    foreach ($instance->getObservableEvents() as $event) {
      static::$dispatcher->forget("netflex.commerce.order.{$event}");
    }

    foreach (array_values($instance->dispatchesEvents) as $event) {
      static::$dispatcher->forget($event);
    }
  }
}
