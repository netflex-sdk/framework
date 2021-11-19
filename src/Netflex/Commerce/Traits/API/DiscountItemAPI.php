<?php

namespace Netflex\Commerce\Traits\API;

use Exception;
use Netflex\API\Facades\API;
use Netflex\Commerce\Order;

trait DiscountItemAPI
{
  /**
   * @throws Exception
   */
  public function delete()
  {
    API::delete(Order::basePath() . $this->order_id . '/discount/' . $this->id);

    $this->getRootParent()->forgetInCache();
  }
}
