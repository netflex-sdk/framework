<?php

use Carbon\Carbon;

if (!function_exists('seconds_until_end_of_today')) {
    /**
     * Calculates the number of seconds until the end of the current day
     * Usefull when caching things that should be refreshed at the end of the day
     *
     * @return int
     */
    function seconds_until_end_of_today()
    {
        return Carbon::today()->endOfDay()->diffInSeconds(Carbon::now());
    }
}
