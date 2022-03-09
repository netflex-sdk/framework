<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

if (!function_exists('md5_to_uuid')) {
    /**
     * Generates a UUID from a md5 hash
     * @return string
     */
    function md5_to_uuid($md5)
    {
        return substr($md5, 0, 8) . '-' .
            substr($md5, 8, 4) . '-' .
            substr($md5, 12, 4) . '-' .
            substr($md5, 16, 4) . '-' .
            substr($md5, 20);
    }
}

if (!function_exists('uuid')) {
    /**
     * Generates a unique id
     * @param string|null $from
     * @return string
     */
    function uuid($from = null)
    {
        $md5 = $from ? $from : (microtime() . uniqid());
        return md5_to_uuid(md5($md5));
    }
}

if (!function_exists('lock_locale')) {
    function lock_locale()
    {
        Config::set('app.localeHasBeenExplicitlySet', true);
    }
}

if (!function_exists('locale_is_locked')) {
    function locale_is_locked()
    {
        return config('app.localeHasBeenExplicitlySet', false);
    }
}

if (!function_exists('seconds_until_end_of_day')) {
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
