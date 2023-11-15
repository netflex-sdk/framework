<?php

namespace Netflex\Render;

use JsonSerializable;

use Netflex\API\Facades\API;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Client\ClientExceptionInterface;
use Netflex\Render\Exceptions\RenderException;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

use Netflex\Render\Contracts\Renderable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\HeaderBag;

abstract class Renderer implements Renderable, Jsonable, JsonSerializable
{
    /** @var string Format */
    protected $format = null;

    /** @var string File extension */
    protected $extension = null;

    /** @var array API options */
    protected $options = [
        'url' => 'about:blank',
        'fetch' => false,
        'cache' => true,
        'options' => [
            'dpi' => 1,
            'timeout' => 60 * 1000,
        ]
    ];

    /**
     * @param string $url
     * @param array $options
     */
    protected function __construct(string $url, $options = [])
    {
        if ($options) {
            $this->options['options'] = array_merge($this->options['options'], $options);
        }

        $this->options['url'] = $url;
    }

    /**
     * @return object
     */
    protected static function status()
    {
        return API::get('foundation/pdf/status');
    }

    /**
     * Get the Chromium version of the renderer
     * 
     * @return string
     */
    public static function version()
    {
        return static::status()
            ->chromium;
    }

    /**
     * @return string|null
     */
    protected function fetchAsUrl()
    {
        $this->options['format'] = $this->format;
        $this->options['fetch'] = false;

        if (!Str::startsWith($this->options['url'], 'data:')) {
            $this->options['time'] = time();
        }

        $render = function () {
            try {
                $response = API::getGuzzleInstance()
                    ->post('foundation/pdf', [
                        'json' => $this->options
                    ]);

                return json_decode($response->getBody())->url;
            } catch (ClientExceptionInterface $exception) {
                if ($exception instanceof BadResponseException) {
                    throw new RenderException($exception->getResponse());
                }

                throw $exception;
            }
        };

        if ($this->options['cache'] ?? false) {
            $hash = md5(json_encode($this->options));
            return Cache::rememberForever('netflex.renderer.' . $this->format . '.' . $hash, fn () => $render());
        }

        return $render();
    }

    /**
     * @return ResponseInterface
     */
    protected function fetch()
    {
        $this->options['format'] = $this->format;
        $this->options['fetch'] = true;

        if (!Str::startsWith($this->options['url'], 'data:')) {
            $this->options['time'] = time();
        }

        $response = API::getGuzzleInstance()
            ->post('foundation/pdf', [
                'json' => $this->options
            ]);

        return $this->postProcess($response);
    }

    /**
     * Set option
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    protected function setOption(string $key, $value)
    {
        if (!array_key_exists('options', $this->options)) {
            $this->options['options'] = [];
        }

        $this->options['options'][$key] = $value;

        if ($value === null && array_key_exists($key, $this->options['options'])) {
            unset($this->options['options'][$key]);
        }

        return $this;
    }

    protected function getOption(string $key, $default = null)
    {
        if (array_key_exists('options', $this->options) && is_array($this->options['options'])) {
            return $this->options['options'][$key] ?? $default;
        }

        return $default;
    }

    /**
     * Sets the devicePixelRatio of the viewport
     *
     * @param float $pixelRatio
     * @return static
     */
    public function devicePixelRatio(float $pixelRatio = 1.0)
    {
        return $this->setOption('dpi', $pixelRatio);
    }

    /**
     * Waits until the entire document, including resources are fully loaded.
     * 
     * @return static
     * */
    public function waitUntilLoaded()
    {
        return $this->setOption('waitUntil', 'load');
    }

    /**
     * Waits until the 'DOMContentLoaded' event is fired.
     * 
     * @return static
     * */
    public function waitUntiDOMContentLoaded()
    {
        return $this->setOption('waitUntil', 'domcontentloaded');
    }

    /**
     * Waits until there has not been any network requests for at least 500ms
     * 
     * @return static
     * */
    public function waitUntiNetworkIdle()
    {
        return $this->setOption('waitUntil', 'networkidle0');
    }

    /**
     * Waits until there has not been more than 2 network requests for at least 500ms
     * 
     * @return static
     * */
    public function waitUntiNetworkSettled()
    {
        return $this->setOption('waitUntil', 'networkidle2');
    }

    /**
     * Maximum amount of time to wait before timing out in milliseconds. Defaults to 30000
     *
     * @param integer $ms
     * @return static
     */
    public function timeout(int $timeout)
    {
        return $this->setOption('timeout', $timeout);
    }

    /**
     * Get the rendered content as a file handle (resource)
     * 
     * @return resource
     */
    public function stream()
    {
        $response = $this->download();
        $resource = tmpfile();
        fwrite($resource, $response->getContent());
        return $resource;
    }

    /**
     * Get the rendered content as a blob
     * 
     * @return string|null
     */
    public function blob()
    {
        if ($content = $this->download()->getContent()) {
            return $content;
        }

        return null;
    }

    /**
     * Retrieve the rendered content as a download response
     *
     * @param string $filename
     * @return Response
     */
    public function download($filename = null): Response
    {
        if ($filename) {
            if (!pathinfo($filename, PATHINFO_EXTENSION)) {
                $extension = $this->extension ?? $this->format ?? null;
                if ($extension) {
                    $filename = implode('.', [rtrim($filename, '.'), $this->extension]);
                }
            }
        }

        $filename = $filename ? ('; filename="' . $filename . '"') : null;

        $request = new Request();
        $request->headers->set('Content-Disposition', 'attachment' . $filename);

        return $this->toResponse($request);
    }

    /**
     * Retrieve the rendered content as a response
     *
     * @param Request $request
     * @return Response
     */
    public function toResponse($request = null)
    {
        $time = microtime(true); // time in Microseconds
        $this->options['fetch'] = true;

        $response = $this->fetch();
        $content = (string) $response->getBody();

        $headers = $request ? $request->headers : new HeaderBag([]);

        $contentType = $response->getHeaderLine('Content-Type');

        return with(new Response($content, 200, $headers->all()))
            ->header('Content-Type', $contentType, true)
            ->header('X-SSR', 1, true)
            ->header('X-SSR-Rendered-In', (microtime(true) - $time) . 's', true);
    }

    /**
     * Retrieve URL to the rendered content
     *
     * @return string
     */
    public function link(): string
    {
        return $this->fetchAsUrl();
    }

    /**
     * Postprocessing stage
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function postProcess(ResponseInterface $response): ResponseInterface
    {
        return $response;
    }

    /**
     * Render from url external or interal
     *
     * @param string|null $url
     * @return static
     */
    public static function url(string $url)
    {
        if (!Str::startsWith($url, 'data:') && !Str::startsWith($url, 'http:') && !Str::startsWith($url, 'https:')) {
            $url = URL::to($url);
        }

        return new static($url);
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

        return static::url('data:text/html;charset=utf-8;base64,' . base64_encode($html));
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
        if (is_string($view)) {
            $view = View::make($view, $data, $mergeData);
        }

        return static::from($view->render());
    }

    /**
     * Render from named route.
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return static
     */
    public static function route($name = null, $parameters = [], $absolute = true)
    {
        return static::url(URL::route($name, $parameters, $absolute));
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->link();
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * @param integer $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
