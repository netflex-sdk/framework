<?php

namespace Netflex\Render\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Netflex\Render\View\MjmlFinder;
 */
class MjmlView extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'mjml.finder';
    }
}
