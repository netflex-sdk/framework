<?php

namespace Netflex\Actions\Contracts\Reservations;

use Illuminate\Http\Request;

/**
 *
 * ReservationBookingControllerContract describes a controller that can react to a user using the drag to add or click to
 * edit functionality on the Reservation/Booking screen in netflex
 *
 */
interface ActionControllerContract
{
    /**
     *
     * Is called when the user submits a form after having dragged to create a new booking in the reservations timeline screen
     * in Netflex.
     *
     * @param Request $request
     * @return mixed
     */
    function create(Request $request);

    /**
     *
     * Is called when the user has clicked an existing reservation in the reservations timeline screen in Netflex
     *
     * @param Request $request
     * @return mixed
     */
    function update(Request $request);


    /**
     *
     * Is Called when the users has clicked to delete a reservation in the reservations timeline screen in Netflex.
     *
     * @param Request $request
     * @return mixed
     */
    function destroy(Request $request);
}
