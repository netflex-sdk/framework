<?php

namespace Netflex\Commerce\Traits\API;

use Exception;
use Netflex\API\Facades\API;
use Netflex\Commerce\Order;

trait LogItemAPI
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
      API::put(Order::basePath() . $this->order_id . '/log/' . $this->id, $updates);

      $this->getRootParent()->forgetInCache();
    }

    $this->modified = [];

    return $this;
  }

  /**
   * @throws Exception
   */
  public function delete()
  {
    API::delete(Order::basePath() . $this->order_id . '/log/' . $this->id);

    $this->getRootParent()->forgetInCache();
  }
}
