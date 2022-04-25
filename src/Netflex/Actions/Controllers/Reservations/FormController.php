<?php

namespace Netflex\Actions\Controllers\Reservations;

use Carbon\CarbonImmutable as Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Netflex\Commerce\Order;
use Netflex\Actions\Contracts\Reservations\BookingFormDescriber;
use Netflex\Structure\Entry;

class FormController extends Controller
{

    public function createForm(Request $r)
    {
        $entry = Entry::find($r->get("id"));

        $from = Carbon::parse($r->get("from"));
        $to = Carbon::parse($r->get("to"));


        if ($r->has("cart_item_id") && $r->has("order_id")) {
            try {
                $order = Order::retrieve($r->get("order_id"));
            } catch (\Exception $ex) {
                return abort(500);
            }

            $cartItem = $order->cart->items
                ->first(fn (Order $ci) => $ci->id == $r->get("cart_item_id"));
            abort_unless($cartItem, 404);

            if ($entry instanceof BookingFormDescriber) {
                return response()->json($entry->getBookingFormEditStructure($order, $cartItem, $from, $to));
            }
        }

        if ($entry instanceof BookingFormDescriber) {
            return response()->json($entry->getBookingFormCreateStructure($from, $to));
        } else {
            return response()->json([]);
        }
    }
}
