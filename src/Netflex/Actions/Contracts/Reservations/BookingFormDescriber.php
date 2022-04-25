<?php

namespace Netflex\Actions\Contracts\Reservations;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Netflex\Commerce\CartItem;
use Netflex\Commerce\Order;

interface BookingFormDescriber
{
    /**
     *
     * Returns an create reservations form.
     *
     * Should contain a list of FormFields
     *
     * @return array
     */
    public function getBookingFormCreateStructure(CarbonInterface $from, CarbonInterface $to): array;


    /**
     *
     * Returns a edit reservation form
     *
     * @param Order $order
     * @param CartItem $item
     * @param Carbon $from
     * @param Carbon $to
     * @return array
     */
    public function getBookingFormEditStructure(Order $order, CartItem $item, CarbonInterface $from, CarbonInterface $to): array;
}
