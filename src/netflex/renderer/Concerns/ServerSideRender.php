<?php

namespace Netflex\Render\Concerns;

use Illuminate\Routing\Controller;
use Netflex\Render\Http\Middleware\SSR;

trait ServerSideRender
{
    public static function bootServerSideRender()
    {
        static::booted(function (Controller $controller) {
            $controller->middleware(SSR::class);
        });
    }
}
