<?php

namespace Netflex\Netflexapp\Contracts\Orders\Refunds;

use Netflex\Netflexapp\Requests\Orders\Refunds\RefundCartItemRequest;
use Netflex\Netflexapp\Requests\Orders\Refunds\RefundOrderRequest;

interface ActionControllerContract
{

    public function refundOrder(RefundOrderRequest $request);

    public function refundCartItem(RefundCartItemRequest $request);
}
