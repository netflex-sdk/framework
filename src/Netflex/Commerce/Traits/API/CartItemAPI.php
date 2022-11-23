<?php

namespace Netflex\Commerce\Traits\API;

use Exception;
use Netflex\API\Facades\API;
use Netflex\Commerce\Order;

trait CartItemAPI
{
  /**
   * @param array $updates
   * @return static
   * @throws Exception
   */
  public function save($updates = [])
  {
    foreach ($this->modified as $modifiedKey) {
      $updates[$modifiedKey] = $this->{$modifiedKey};
    }

    if (!empty($updates)) {
      API::put(Order::basePath() . $this->order_id . '/cart/' . $this->id, $updates);

      if ($rootParent = $this->getRootParent()) {
        $rootParent->forgetInCache();
      }
    }

    $this->modified = [];

    return $this;
  }

  /**
   * @throws Exception
   */
  public function delete()
  {
    API::delete(Order::basePath() . $this->order_id . '/cart/' . $this->id);

    if ($rootParent = $this->getRootParent()) {
      $rootParent->forgetInCache();
    }
  }

  public function addDiscount($item)
  {
    if (!isset($item['scope_key'])) {
      $item['scope_key'] = $this->id;
    }

    API::post(Order::basePath() . $this->order_id . '/discount/' . $this->id, $item);
  }
}
