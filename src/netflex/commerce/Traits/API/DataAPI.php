<?php

namespace Netflex\Commerce\Traits\API;

use Exception;
use Netflex\API\Facades\API;
use Netflex\Commerce\Order;

trait DataAPI
{
  /**
   * @param $key
   * @throws Exception
   */
  public function delete($key)
  {
    API::delete(Order::basePath() . $this->parent->id . '/data/' . $key);

    $this->getRootParent()->forgetInCache();
  }
}
