<?php

namespace Netflex\Render;

use Exception;
use Throwable;
use ErrorException;
use InvalidArgumentException;

use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Netflex\Render\Facades\MjmlView;

class MJML extends Renderer
{
    /** @var string Format */
    protected $format = 'mjml';

    /** @var string File extension */
    protected $extension = 'html';

    protected $blob = null;

    protected static function withBlob($blob)
    {
        $instance = new static('');
        $instance->blob = $blob;

        return $instance;
    }

    public function blob()
    {
        if ($this->blob) {
            return $this->blob;
        }

        return parent::blob();
    }

    /**
     * Render from a view
     *
     * @param View|string $view
     * @param \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param array $mergeData
     * @return static
     */
    public static function view($view, $data = [], $mergeData = [])
    {
        if ($view instanceof View) {
            /** @var View $view */
            return static::from($view->render());
        }

        // Check if a MJML view file exists
        $path = MjmlView::find($view);

        if (file_exists($path)) {
            $hash = hash_file('md5', $path); // Key used for caching the MJML template

            // We render the raw MJML view as HTML, preserving Blade syntax
            // We than pass the rendered view to Blade and gets PHP code back.
            // The result is cached using the mjml files hash as key,
            // So if the templates changes, we recompile the template.
            $php = Cache::rememberForever($hash, function () use ($path) {
                $content = file_get_contents($path);
                return Blade::compileString(static::from($content)->blob());
            });

            // We extract the variables into scope, and evaluates the compiled view
            $data = array_merge($data, $mergeData);

            $obLevel = ob_get_level();

            ob_start();
            extract($data, EXTR_SKIP);

            try {
                eval('?' . '>' . $php);
            } catch (Exception $e) {
                while (ob_get_level() > $obLevel) {
                    ob_end_clean();
                }

                throw $e;
            } catch (Throwable $e) {
                while (ob_get_level() > $obLevel) {
                    ob_end_clean();
                }

                throw new ErrorException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getPrevious());
            }

            $output = ob_get_clean(); // The rendered content with variables replaced.

            // To maintain compatiblity with the other renderers, we return a new instance of
            // MJML, but we hardcode the "blob" with the rendered content.
            // This still allows us to return the MJML instance directly as a response
            return static::withBlob($output);
        }

        // MJML specific view not found, attempt to render the first found blade or php view
        // and pass to content to the MJML renderer

        return static::from(view($view, $data, $mergeData)->render());
    }

    /**
     * Render from raw markup
     *
     * @param HtmlString|string $html
     * @return static
     */
    public static function from($html)
    {
        if ($html instanceof HtmlString) {
            $html = $html->__toString();
        }

        return static::url('data:text/mjml;base64,' . base64_encode($html));
    }
}
