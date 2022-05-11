<?php

namespace Netflex\Foundation\Wallet;

use Carbon\Carbon;
use DateTimeInterface;
use JsonSerializable;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Netflex\API\Facades\API;
use Symfony\Component\HttpFoundation\HeaderBag;
use TypeError;

class PKPass implements Responsable, JsonSerializable, Jsonable
{
    const FORMAT_QR = 'PKBarcodeFormatQR';
    const FORMAT_PDF417 = 'PKBarcodeFormatPDF417';
    const FORMAT_AZTEC = 'PKBarcodeFormatAztec';
    const FORMAT_CODE128 = 'PKBarcodeFormatCode128';

    const TYPE_GENERIC = 'generic';
    const TYPE_BOARDINGPASS = 'boardingPass';
    const TYPE_STORECARD = 'storeCard';
    const TYPE_EVENTTICKET = 'eventTicket';
    const TYPE_COUPON = 'coupon';

    const FIELD_HEADER = 'headerFields';
    const FIELD_PRIMARY = 'primaryFields';
    const FIELD_SECONDARY = 'secondaryFields';
    const FIELD_AUXILIARY = 'auxiliaryFields';
    const FIELD_BACK = 'backFields';

    const DATA_DETECTOR_PHONE_NUMBER = 'PKDataDetectorTypePhoneNumber';
    const DATA_DETECTOR_LINK = 'PKDataDetectorTypeLink';
    const DATA_DETECTOR_ADDRESS = 'PKDataDetectorTypeAddress';
    const DATA_DETECTOR_CALENDAR_EVENT = 'PKDataDetectorTypeCalendarEvent';

    const TEXT_ALIGNMENT_LEFT = 'PKTextAlignmentLeft';
    const TEXT_ALIGNMENT_CENTER = 'PKTextAlignmentCenter';
    const TEXT_ALIGNMENT_RIGHT = 'PKTextAlignmentRight';

    const NUMBER_STYLE_DECIMAL = 'PKNumberStyleDecimal';
    const NUMBER_STYLE_PERCENT = 'PKNumberStylePercent';
    const NUMBER_STYLE_SCIENTIFIC = 'PKNumberStyleScientific';
    const NUMBER_STYLE_SPELLOUT = 'PKNumberStyleSpellOut';

    const TRANSIT_TYPE_AIR = 'PKTransitTypeAir';
    const TRANSIT_TYPE_BOAT = 'PKTransitTypeBoat';
    const TRANSIT_TYPE_BUS = 'PKTransitTypeBus';
    const TRANSIT_TYPE_GENERIC = 'PKTransitTypeGeneric';
    const TRANSIT_TYPE_TRAIN = 'PKTransitTypeTrain';

    const DATE_STYLE_NONE = 'PKDateStyleNone';
    const DATE_STYLE_SHORT = 'PKDateStyleShort';
    const DATE_STYLE_MEDIUM = 'PKDateStyleMedium';
    const DATE_STYLE_LONG = 'PKDateStyleLong';
    const DATE_STYLE_FULL = 'PKDateStyleFull';

    protected $type = self::TYPE_GENERIC;

    protected $data = [
        'formatVersion' => 1,
    ];

    protected $files = [];

    protected $fields = [
        'headerFields' => [],
        'primaryFields' => [],
        'secondaryFields' => [],
        'auxiliaryFields' => [],
        'backFields' => [],
    ];

    protected function __construct(string $type = PKPass::TYPE_GENERIC, array $data = [], array $fields = [])
    {
        $this->type = $type;

        $this->data = array_merge([
            'formatVersion' => 1,
        ], $data);

        $this->fields = $fields ? $fields : [
            'headerFields' => [],
            'primaryFields' => [],
            'secondaryFields' => [],
            'auxiliaryFields' => [],
            'backFields' => [],
        ];
    }

    public static function boardingpass()
    {
        return new static(self::TYPE_BOARDINGPASS);
    }

    public static function storeCard()
    {
        return new static(self::TYPE_STORECARD);
    }

    public static function eventTicket()
    {
        return new static(self::TYPE_EVENTTICKET);
    }

    public static function coupon()
    {
        return new static(self::TYPE_COUPON);
    }

    public static function generic()
    {
        return new static(self::TYPE_GENERIC);
    }

    /**
     * @param string $type
     * @return static
     */
    public static function make(string $type = PKPass::TYPE_GENERIC)
    {
        return new static($type);
    }

    public function associatedStoreIdentifier(int $storeIdentifier)
    {
        $this->data['associatedStoreIdentifiers'] = $this->data['associatedStoreIdentifiers'] ?? [];
        $this->data['associatedStoreIdentifiers'][] = [$storeIdentifier];
        return $this;
    }

    public function expirationDate($date)
    {
        if ($date instanceof DateTimeInterface) {
            $date = Carbon::parse($date)->toIso8601ZuluString();
        }

        $this->data['expirationDate'] = $date;

        return $this;
    }

    public function relevantDate($date)
    {
        if ($date instanceof DateTimeInterface) {
            $date = Carbon::parse($date)->toIso8601ZuluString();
        }

        $this->data['relevantDate'] = $date;

        return $this;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type)
    {
        if (!in_array($type, [self::TYPE_GENERIC, self::TYPE_BOARDINGPASS, self::TYPE_STORECARD, self::TYPE_EVENTTICKET])) {
            throw new InvalidArgumentException('Invalid type');
        }

        if ($type !== $this->type) {
            return new static($type, $this->data, $this->fields);
        }

        return $this;
    }

    public function transitType(string $transitType)
    {
        if ($this->type === static::TYPE_BOARDINGPASS) {
            if (in_array($transitType, [self::TRANSIT_TYPE_AIR, self::TRANSIT_TYPE_BOAT, self::TRANSIT_TYPE_BUS, self::TRANSIT_TYPE_GENERIC, self::TRANSIT_TYPE_TRAIN])) {
                $this->fields['transitType'] = $transitType;
            }

            return $this;
        }

        throw new InvalidArgumentException('This pass type does not support transit type');
    }

    /**
     * @param string $key
     * @param mixed $valu
     * @param string $location Where is the field located on the pass
     * @param string|array $options Label or options array
     * @return static
     */
    protected function addField(string $key, $value, string $location = PKPass::FIELD_PRIMARY, $options = [])
    {
        if ($value instanceof DateTimeInterface) {
            $value = Carbon::parse($value)->toIso8601ZuluString();
        }

        if ($value instanceof HtmlString) {
            $value = $value->toHtml();
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $value = (string) $value;
        }

        $field = [
            'key' => $key,
            'value' => $value,
        ];

        if (is_string($options)) {
            $options = [
                'label' => $options,
            ];
        }

        foreach ($options as $optionKey => $optionValue) {
            if ($optionValue instanceof HtmlString) {
                $optionValue = $optionValue->toHtml();
            }

            if (is_object($optionValue) && method_exists($optionValue, '__toString')) {
                $optionValue = (string) $optionValue;
            }

            switch ($optionKey) {
                case 'isRelative':
                    $field[$optionKey] = (bool) $optionValue;
                    break;
                case 'dateStyle':
                case 'timeStyle':
                    if (in_array($optionValue, [self::DATE_STYLE_NONE, self::DATE_STYLE_SHORT, self::DATE_STYLE_MEDIUM, self::DATE_STYLE_LONG, self::DATE_STYLE_FULL])) {
                        $field[$optionKey] = $optionValue;
                    }
                    break;
                case 'attributedValue':
                case 'changeMessage':
                case 'label':
                    $field[$optionKey] = $optionValue;
                    break;
                case 'dataDetectorTypes':
                    $dataDetectorTypes = [];
                    if (!is_array($optionValue)) {
                        $optionValue = [$optionValue];
                    }

                    foreach ($optionValue as $dataDetectorType) {
                        if (in_array($optionValue, [static::DATA_DETECTOR_PHONE_NUMBER, static::DATA_DETECTOR_LINK, static::DATA_DETECTOR_ADDRESS, static::DATA_DETECTOR_CALENDAR_EVENT])) {
                            $dataDetectorTypes[] = $dataDetectorType;
                        }
                    }

                    $field[$optionKey] = $dataDetectorTypes;
                    break;
                case 'textAlignment':
                    if (in_array($optionValue, [static::TEXT_ALIGNMENT_LEFT, static::TEXT_ALIGNMENT_CENTER, static::TEXT_ALIGNMENT_RIGHT])) {
                        $field[$optionKey] = $optionValue;
                    }
                    break;
                case 'currencyCode':
                    if (is_int($value) || is_float($value)) {
                        $field[$optionKey] = $optionValue;
                    }
                    break;
                case 'numberStyle':
                    if ((is_int($value) || is_float($value)) && in_array($optionValue, [static::NUMBER_STYLE_DECIMAL, static::NUMBER_STYLE_PERCENT, static::NUMBER_STYLE_SCIENTIFIC, static::NUMBER_STYLE_SPELLOUT])) {
                        $field[$optionKey] = $optionValue;
                    }
                    break;
                case 'semantics':
                    if (is_array($optionValue)) {
                        $field[$optionKey] = $optionValue;
                    }
                    break;
                default:
                    break;
            }
        }

        $this->fields[$location] = $this->fields[$location] ?? [];
        $this->fields[$location][] = $field;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|array $options Label or options array
     * @return static
     */
    public function addHeaderField(string $key, $value, $options = [])
    {
        return $this->addField($key, $value, static::FIELD_HEADER, $options);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|array $options Label or options array
     * @return static
     */
    public function addPrimaryField(string $key, $value, $options = [])
    {
        return $this->addField($key, $value, static::FIELD_PRIMARY, $options);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|array $options Label or options array
     * @return static
     */
    public function addSecondaryField(string $key, $value, $options = [])
    {
        return $this->addField($key, $value, static::FIELD_SECONDARY, $options);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|array $options Label or options array
     * @return static
     */
    public function addAuxiliaryField(string $key, $value, $options = [])
    {
        return $this->addField($key, $value, static::FIELD_AUXILIARY, $options);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string|array $options Label or options array
     * @return static
     */
    public function addBackField(string $key, $value, $options = [])
    {
        return $this->addField($key, $value, static::FIELD_BACK, $options);
    }

    public function organizationName(string $organizationName)
    {
        $this->data['organizationName'] = $organizationName;
        return $this;
    }

    public function sharingProhibited(bool $sharingProhibited = true)
    {
        $this->data['sharingProhibited'] = $sharingProhibited;
        return $this;
    }

    public function serialNumber(string $serialNumber)
    {
        $this->data['serialNumber'] = $serialNumber;
        return $this;
    }

    public function description(string $description)
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * Adds a relevant location for which to automatiocally suggest to display the pass
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $name
     * @return static
     */
    public function addLocation(float $latitude, float $longitude, ?string $name = null)
    {
        $this->data['locations'] = $this->data['locations'] ?? [];

        $location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($name) {
            $location['relevantText'] = $name;
        }

        $this->data['locations'][] = $location;

        return $this;
    }

    /**
     * The maximum distance in meter from the relevant location(s) to trigger pass suggestion
     *
     * @param integer $maxDistance
     * @return static
     */
    public function maxDistance(int $maxDistance)
    {
        $this->data['maxDistance'] = $maxDistance;
        return $this;
    }

    /**
     * @param string $color
     * @return static
     */
    public function foregroundColor(string $color)
    {
        $this->data['foregroundColor'] = $color;
        return $this;
    }

    /**
     * @param string $color
     * @return static
     */
    public function backgroundColor(string $color)
    {
        $this->data['backgroundColor'] = $color;
        return $this;
    }

    /**
     * @param string $color
     * @return static
     */
    public function labelColor(string $color)
    {
        $this->data['labelColor'] = $color;
        return $this;
    }

    /**
     * @param string $color
     * @return static
     */
    public function stripColor(string $color)
    {
        $this->data['stripColor'] = $color;
        return $this;
    }

    /**
     * @param string $color
     * @return static
     */
    public function logoText(string $logoText)
    {
        $this->data['logoText'] = $logoText;
        return $this;
    }

    /**
     * @param string $barcode
     * @param string|null $altText
     * @param string $format
     * @return static
     */
    public function barcode(string $barcode, ?string $altText = null, string $format = PKPass::FORMAT_QR)
    {
        if (!$altText) {
            $altText = $barcode;
        }

        $this->data['barcode'] = [
            'format' => $format,
            'message' => $barcode,
            'altText' => $altText,
            'messageEncoding' => 'iso-8859-1',
        ];

        return $this;
    }

    public function addFile(string $name, string $path)
    {
        $this->files[] = ['name' => $name, 'path' => $path];
    }

    /**
     * @param string $filename
     * @return Response
     */
    public function download($filename = null): Response
    {
        $request = new Request();
        $request->headers->set('Content-Disposition', 'attachment' . $filename);

        return $this->toResponse($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        $time = microtime(true); // time in Microseconds

        $response = API::getGuzzleInstance()
            ->post('foundation/wallet/pkpass', [
                'json' => [
                    'data' => $this->jsonSerialize(),
                    'files' => $this->files
                ]
            ]);

        $content = (string) $response->getBody();
        $headers = $request ? $request->headers : new HeaderBag([]);

        return with(new Response($content, 200, $headers->all()))
            ->header('Content-Type', 'application/vnd.apple.pkpass', true)
            ->header('X-SSR', 1, true)
            ->header('X-SSR-Rendered-In', (microtime(true) - $time) . 's', true);
    }

    protected function generateSerialNumber(): string
    {
        return Str::uuid();
    }

    protected function payload(): array
    {
        $data = $this->data;
        $data[$this->type] = [];

        foreach ($this->fields as $field => $fields) {
            if (!is_array($fields) || count($fields)) {
                $data[$this->type][$field] = $fields;
            }
        }

        if (!($data['serialNumber'] ?? null)) {
            $data['serialNumber'] = $this->generateSerialNumber();
        }

        return $data;
    }

    public function jsonSerialize()
    {
        return $this->payload();
    }

    public static function schema()
    {
        $schema = file_get_contents(__DIR__ . '/PKPass.schema.json');
        return json_decode($schema);
    }

    public function validate()
    {
        $validator = new Validator;
        $data = $this->payload();
        $data = json_decode(json_encode($data));
        $validator->validate($data, static::schema(), Constraint::CHECK_MODE_COERCE_TYPES);

        if ($validator->numErrors() > 0) {
            foreach ($validator->getErrors() as $error) {
                throw new TypeError('[' . $error['property'] . '] ' . $error['message']);
            }
        }

        return true;
    }

    public function toJson($options = 0)
    {
        return json_encode($this, $options);
    }
}
