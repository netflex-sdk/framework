<?php

namespace Netflex\Actions\Contracts\Orders\Refunds;

use Netflex\Actions\Requests\Orders\Refunds\RefundCartItemRequest;
use Netflex\Actions\Requests\Orders\Refunds\RefundOrderRequest;

interface ActionControllerContract
{

    /**
     *
     * Refunds a single order
     *
     * If you need to return an error to the user use the abort method. The abort message will be shown to the user
     *
     * @param RefundOrderRequest $request
     * @return mixed
     */
    public function refundOrder(RefundOrderRequest $request);

    /**
     * Refunds a partial cart item
     *
     * If you need to return an error to the user use the abort method. The abort message will be shown to the user
     *
     * @param RefundCartItemRequest $request
     * @return mixed
     */
    public function refundCartItem(RefundCartItemRequest $request);
}
