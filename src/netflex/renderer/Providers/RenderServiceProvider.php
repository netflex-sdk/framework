<?php

namespace Netflex\Render\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

use Netflex\Render\View\MjmlViewFinder;

use Netflex\Render\HTML;
use Netflex\Render\MJML;
use Netflex\Render\JPG;
use Netflex\Render\PDF;
use Netflex\Render\PNG;

use Netflex\Render\View\Directives;

class RenderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('mjml.finder', function ($app) {
            return new MjmlViewFinder($app['files'], $app['config']['view.paths']);
        });
    }

    public function boot()
    {
        $this->registerMacros();
        $this->registerDirectives();
    }

    public function registerMacros()
    {
        $renderers = [
            HTML::class,
            MJML::class,
            JPG::class,
            PDF::class,
            PNG::class,
        ];

        foreach ($renderers as $renderer) {
            $type = basename(str_replace('\\', '/', $renderer));

            View::macro('render' . $type, function () use ($renderer) {
                /** @var \Netflex\Render\Renderer $renderer */
                return $renderer::view($this);
            });
        }
    }

    public function registerDirectives()
    {
        Directives::register();
    }
}
