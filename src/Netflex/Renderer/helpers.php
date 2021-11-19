<?php

use Illuminate\Http\Request;

if (!function_exists('request_ssr_key')) {
    /**
     * Creates a unique key from a request
     *
     * @param Request $request
     * @return string
     */
    function request_ssr_key(Request $request)
    {
        $parameters = http_build_query($request->all());

        $parts = [
            get_class($request),
            $request->method(),
            $request->url(),
            $parameters,
        ];

        if ($user = $request->user()) {
            $parts[] = $user->id;
        }

        $parts = array_values(array_filter($parts));

        return md5(implode('|', $parts));
    }
}
