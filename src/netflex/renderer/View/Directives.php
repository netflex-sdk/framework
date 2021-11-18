<?php

namespace Netflex\Render\View;

use Closure;
use Illuminate\Support\Facades\Blade;

class Directives
{
    const PREFIX = 'pdf';

    protected static function date()
    {
        return function () {
            return '<span class="date"></span>';
        };
    }

    protected static function title()
    {
        return function () {
            return '<span class="title"></span>';
        };
    }

    protected static function url()
    {
        return function () {
            return '<span class="url"></span>';
        };
    }

    protected static function page_number()
    {
        return function () {
            return '<span class="pageNumber"></span>';
        };
    }

    protected static function total_pages()
    {
        return function () {
            return '<span class="totalPages"></span>';
        };
    }

    protected static function page_break()
    {
        return static::page_break_after();
    }

    protected static function page_break_before()
    {
        return function () {
            return '<div style="page-break-before: always"></div>';
        };
    }

    protected static function page_break_before_avoid()
    {
        return function () {
            return '<div style="page-break-before: avoid"></div>';
        };
    }

    protected static function page_break_after()
    {
        return function () {
            return '<div style="page-break-after: always"></div>';
        };
    }

    protected static function page_break_after_avoid()
    {
        return function () {
            return '<div style="page-break-after: avoid"></div>';
        };
    }

    public static function register()
    {
        $directives = [
            'date',
            'title',
            'url',
            'page_number',
            'total_pages',
            'page_break',
            'page_break_before',
            'page_break_before_avoid',
            'page_break_after',
            'page_break_after_avoid',
        ];

        foreach ($directives as $directive) {
            $name = implode('_', [static::PREFIX, $directive]);

            /** @var Closure */
            $handler = forward_static_call([static::class, $directive]);

            Blade::directive($name, $handler);
        }
    }
}
