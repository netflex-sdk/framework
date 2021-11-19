<?php

namespace Netflex\Render;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

use Netflex\Render\PSR7\Stream;
use Psr\Http\Message\ResponseInterface;

use Composer\InstalledVersions;
use DateTime;

class PDF extends Renderer
{
    /** @var string Format */
    protected $format = 'pdf';

    /** @var array Default PDF tags */
    protected $tags = [];

    /** @var string */
    const DEFAULT_CREATOR = 'Apility AS';

    /** @var string */
    const DEFAULT_MARGIN = '1cm';

    /** @var string Pixel */
    const UNIT_PX = 'px';

    /** @var string Inch */
    const UNIT_IN = 'in';

    /** @var string Centimeter - Default unit */
    const UNIT_CM = 'cm';

    /** @var string Millimetre */
    const UNIT_MM = 'mm';

    /** @var string A0 paper size */
    const FORMAT_A0 = 'A0';

    /** @var string A1 paper size */
    const FORMAT_A1 = 'A1';

    /** @var string A2 paper size */
    const FORMAT_A2 = 'A2';

    /** @var string A3 paper size */
    const FORMAT_A3 = 'A3';

    /** @var string A4 paper size - Default paper size */
    const FORMAT_A4 = 'A4';

    /** @var string A5 paper size */
    const FORMAT_A5 = 'A5';

    /** @var string A6 paper size */
    const FORMAT_A6 = 'A6';

    /** @var string US letter paper size */
    const FORMAT_LETTER = 'Letter';

    /** @var string US legal paper size */
    const FORMAT_LEGAL = 'Legal';

    /** @var string US tabloid paper size */
    const FORMAT_TABLOID = 'Tabloid';

    /** @var string US ledger paper size */
    const FORMAT_LEDGER = 'Ledger';

    /**
     * @param string $url
     * @param array $options
     */
    protected function __construct(string $url, $options = [])
    {
        parent::__construct($url, $options);
        $this->emulatedMedia(null);
        $this->margin(static::DEFAULT_MARGIN);
        $this->creator(static::DEFAULT_CREATOR);
        $this->application($this->getVersion());
    }

    /**
     * Gets the application version string
     *
     * @return string
     */
    final protected function getVersion()
    {
        $appName = str_replace('\\', '/', PDF::class);
        $packageVersion = InstalledVersions::getPrettyVersion('netflex/renderer');
        return implode(' ', [$appName, $packageVersion]);
    }

    /**
     * Enable the display of header and footer
     *
     * @param boolean $displayHeaderFooter
     * @return static
     */
    public function displayHeaderFooter(bool $displayHeaderFooter = true)
    {
        return $this->setOption('displayHeaderFooter', $displayHeaderFooter);
    }

    /**
     * Sets the HTML template for the print header.
     *
     * @param HtmlString|string $template
     * @return static
     */
    public function headerTemplateFrom($template)
    {
        if ($template instanceof HtmlString) {
            $template = $template->__toString();
        }

        $this->displayHeaderFooter();

        return $this->setOption('headerTemplate', $template);
    }

    /**
     * Sets the HTML template for the print header from a blade view
     *
     * @param View|string $view
     * @param \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param array $mergeData
     * @return static
     */
    public function headerTemplate($view, $data = [], $mergeData = [])
    {
        if (is_string($view)) {
            $view = View::make($view, $data, $mergeData);
        }

        $content = $view->render();

        return $this->headerTemplateFrom($content);
    }

    /**
     * Sets the HTML template for the print footer.
     *
     * @param HtmlString|string $template
     * @return static
     */
    public function footerTemplateFrom($template)
    {
        if ($template instanceof HtmlString) {
            $template = $template->__toString();
        }

        $this->displayHeaderFooter();

        return $this->setOption('footerTemplate', $template);
    }

    /**
     * Sets the HTML template for the print footer from a blade view
     *
     * @param View|string $view
     * @param \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param array $mergeData
     * @return static
     */
    public function footerTemplate($view, $data = [], $mergeData = [])
    {
        if (is_string($view)) {
            $view = View::make($view, $data, $mergeData);
        }

        $content = $view->render();

        return $this->footerTemplateFrom($content);
    }

    /**
     * Paper ranges to print, e.g., [1, 5], 8, [11, 13]
     *
     * @param array|int ...$ranges
     * @return static
     */
    public function pageRanges(...$ranges)
    {
        $value = array_reduce($ranges, function ($values, $range) {
            if (is_array($range)) {
                $values[] = implode('-', $range);
                return $values;
            }

            $values[] = $range;

            return $values;
        }, []);

        return $this->setOption('pageRanges', implode(', ', $value));
    }

    /**
     * Print background graphics. Defaults to false
     *
     * @param boolean $printBackground
     * @return static
     */
    public function printBackground(bool $printBackground = true)
    {
        return $this->setOption('printBackground', $printBackground);
    }

    /**
     * Paper top margin
     *
     * @param int|string $marginTop
     * @param string $unit
     * @return static
     */
    public function marginTop($marginTop, $unit = PDF::UNIT_CM)
    {
        $margin = $this->getOption('margin', []);
        $margin['top'] = static::appendUnit($marginTop, $unit);
        return $this->setOption('margin', $margin);
    }

    /**
     * Paper right margin
     *
     * @param int|string $marginRight
     * @param string $unit
     * @return static
     */
    public function marginRight($marginRight, $unit = PDF::UNIT_CM)
    {
        $margin = $this->getOption('margin', []);
        $margin['right'] = static::appendUnit($marginRight, $unit);
        return $this->setOption('margin', $margin);
    }

    /**
     * Paper bottom margin
     *
     * @param int|string $marginBottom
     * @param string $unit
     * @return static
     */
    public function marginBottom($marginBottom, $unit = PDF::UNIT_CM)
    {
        $margin = $this->getOption('margin', []);
        $margin['bottom'] = static::appendUnit($marginBottom, $unit);
        return $this->setOption('margin', $margin);
    }

    /**
     * Paper left margin
     *
     * @param int|string $marginLeft
     * @param string $unit
     * @return static
     */
    public function marginLeft($marginLeft, $unit = PDF::UNIT_CM)
    {
        $margin = $this->getOption('margin', []);
        $margin['left'] = static::appendUnit($marginLeft, $unit);
        return $this->setOption('margin', $margin);
    }

    /**
     * Paper margins
     *
     * @param int|string ...$values
     * @return static
     */
    public function margin(...$values)
    {
        switch (count($values)) {
            case 1:
                $this->marginTop($values[0]);
                $this->marginRight($values[0]);
                $this->marginBottom($values[0]);
                return $this->marginLeft($values[0]);
            case 2:
                $this->marginTop($values[0]);
                $this->marginRight($values[1]);
                $this->marginBottom($values[0]);
                return $this->marginLeft($values[1]);
            case 3:
                $this->marginTop($values[0]);
                $this->marginRight($values[1]);
                return $this->marginBottom($values[2]);
            case 4:
                $this->marginTop($values[0]);
                $this->marginRight($values[1]);
                $this->marginBottom($values[2]);
                return $this->marginLeft($values[3]);
            default:
                return $this;
        }
    }

    /**
     * Paper format. If set, takes priority over width or height options. Defaults to 'A4'
     *
     * @param string $format
     * @return static
     */
    public function format(string $format = PDF::FORMAT_A4)
    {
        if (in_array($format, static::formats())) {
            if ($this->getOption('width') || $this->getOption('height')) {
                $this->setOption('width', null);
                $this->setOption('height', null);
            }

            return $this->setOption('format', $format);
        }

        return $this;
    }

    /**
     * Appends the given unit to value if the unit is valid
     *
     * @param int|string $value
     * @param string|null $unit
     * @return int|string
     */
    protected static function appendUnit($value, $unit = null)
    {
        if (is_string($value)) {
            foreach (static::units() as $validUnit) {
                if (Str::endsWith($value, $validUnit)) {
                    return $value;
                }
            }
        }

        if (in_array($unit, static::units())) {
            return ((string) $value) . $unit;
        }

        return $value;
    }

    /**
     * Sets the media emulation. Defaults to NULL
     *
     * @param string|null $media 'screen', 'print', or NULL
     * @return static
     */
    public function emulatedMedia($media = null)
    {
        if (is_null($media) || in_array($media, ['screen', 'print'])) {
            return $this->setOption('emulatedMedia', $media);
        }

        return $this;
    }

    /**
     * Paper width
     *
     * @param integer $width
     * @return static
     */
    public function width(int $width)
    {
        if ($this->getOption('format')) {
            $this->setOption('format', null);

            if (!$this->getOption('height')) {
                $this->setOption('height', $width);
            }
        }

        return $this->setOption('width', $width);
    }

    /**
     * Paper height
     *
     * @param integer $width
     * @return static
     */
    public function height(int $height)
    {
        if ($this->getOption('format')) {
            $this->setOption('format', null);

            if (!$this->getOption('width')) {
                $this->setOption('width', $height);
            }
        }

        return $this->setOption('height', $height);
    }

    /**
     * Whether or not to prefer page size as defined by css. Defaults to false, in which case the content will be scaled to fit the paper size.
     *
     * @param boolean $preferCSSPageSize
     * @return static
     */
    public function preferCSSPageSize(bool $preferCSSPageSize = true)
    {
        return $this->setOption('preferCSSPageSize', $preferCSSPageSize);
    }

    /**
     * Paper orientation
     *
     * @param boolean $landscape
     * @return static
     */
    public function landscape(bool $landscape = true)
    {
        return $this->setOption('landscape', $landscape);
    }

    /**
     * Sets the document scaling factor
     *
     * @param float $scale Between 0.1 - 2.0
     * @return static
     */
    public function scale(float $scale = 1.0)
    {
        return $this->setOption('scale', $scale);
    }

    /**
     * Get a list of supported units
     *
     * @return string[]
     */
    public static function units()
    {
        return [
            static::UNIT_PX,
            static::UNIT_MM,
            static::UNIT_CM,
            static::UNIT_IN,
        ];
    }

    /**
     * Get a list of supported paper formats
     *
     * @return string[]
     */
    public static function formats()
    {
        return [
            static::FORMAT_A0,
            static::FORMAT_A1,
            static::FORMAT_A2,
            static::FORMAT_A3,
            static::FORMAT_A4,
            static::FORMAT_A5,
            static::FORMAT_A6,
            static::FORMAT_LETTER,
            static::FORMAT_LEGAL,
            static::FORMAT_TABLOID,
            static::FORMAT_LEDGER,
        ];
    }

    /**
     * Sets a PDF meta tag
     *
     * @param string $tag
     * @param string|null $value
     * @return static
     */
    protected function setTag(string $tag, ?string $value)
    {
        $this->tags[$tag] = $value;
        return $this;
    }

    /**
     * Gets a PDF meta tag value
     *
     * @param string $tag
     * @param string|null $default
     * @return string|null
     */
    protected function getTag(string $tag, ?string $default = null)
    {
        return $this->tags[$tag] ?? $default ?? null;
    }

    /**
     * Sets the PDF author meta
     *
     * @param string $author
     * @return static
     */
    public function author(string $author)
    {
        return $this->setTag('Author', $author);
    }

    /**
     * Sets the PDF description meta
     *
     * @param string $description
     * @return static
     */
    public function description(string $description)
    {
        return $this->setTag('Subject', $description);
    }

    /**
     * Sets the PDF keywords meta
     *
     * @param array $keywords
     * @return void
     */
    public function keywords(array $keywords)
    {
        return $this->setTag('Keywords', implode('; ', array_values($keywords)));
    }

    /**
     * Adds a PDF meta keyword
     *
     * @param string $keyword
     * @return static
     */
    public function keyword(string $keyword)
    {
        $keywords = array_values(array_filter(explode('; ', $this->getTag('Keywords', ''))));
        $keywords[] = $keyword;
        $keywords = array_unique($keywords);
        return $this->keywords($keywords);
    }

    /**
     * Sets the PDF content creator meta
     *
     * @param string $creator
     * @return static
     */
    public function creator(string $creator)
    {
        return $this->setTag('Creator', $creator);
    }

    /**
     * Sets the PDF application meta
     *
     * @param string $creator
     * @return static
     */
    public function application(string $application)
    {
        return $this->setTag('Producer', $application);
    }

    /**
     * Sets the PDF creation date
     *
     * @param DateTime|Carbon $date
     * @return static
     */
    public function created(DateTime $date)
    {
        $date = new Carbon($date);
        return $this->setTag('CreationDate', 'D:' . $date->format('YmdHis') . "+00'00'");
    }

    /**
     * Sets the PDF last modified date
     *
     * @param DateTime|Carbon $date
     * @return static
     */
    public function modified(DateTime $date)
    {
        $date = new Carbon($date);
        return $this->setTag('ModDate', 'D:' . $date->format('YmdHis') . "+00'00'");
    }

    /**
     * Postprocessing stage
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function postProcess(ResponseInterface $response): ResponseInterface
    {
        $now = Carbon::now();

        if (!$this->getTag('CreationDate')) {
            $this->created($now);
        }

        if (!$this->getTag('ModDate')) {
            $this->modified($now);
        }

        $customTags = Collection::make($this->tags);

        if ($customTags->count() > 0) {
            $content = (string) $response->getBody();

            // Extract existing PDF meta tags
            $start = strpos($content, 'obj') + strlen('obj');
            $end = strpos($content, 'endobj', $start);
            $length = $end - $start;

            $tags = substr($content, $start, $length);
            $tags = substr($tags, strpos($tags, '<<') + strlen('<<'));
            $tags = substr($tags, 0, strpos($tags, '>>'));
            $tags = explode("\n", $tags);

            $tags = Collection::make($tags)
                ->filter(function ($tag) {
                    return preg_match('/\/\w+ \([^)]+\)/', $tag) !== false;
                })
                ->mapWithKeys(function ($tag) {
                    $matches = [];
                    preg_match('/\/(?<tag>\w+) \((?<value>[^\)]+)\)/', $tag, $matches);
                    return [$matches['tag'] => $matches['value']];
                });

            // Overwrite PDF meta tags with extracted tags, and our custom tags
            $tags = $tags->merge($customTags)
                ->filter()
                ->map(function ($value, $tag) {
                    $tag = str_replace(' ', "\xc2\xa0", $tag);
                    $value = str_replace('(', '\(', $value);
                    $value = str_replace(')', '\)', $value);
                    return '/' . $tag . ' (' . $value . ')';
                })->join("\n");

            $content = substr_replace($content, "\n<<" . $tags . '>>\n', $start, $length);

            return $response->withBody(Stream::make($content));
        }

        return $response;
    }
}
