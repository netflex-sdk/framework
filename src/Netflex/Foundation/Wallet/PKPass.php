<?php

namespace Netflex\Foundation\Wallet;

use Carbon\Carbon;
use DateTimeInterface;
use JsonSerializable;

use Illuminate\Http\UploadedFile;
use Illuminate\Http\File;
use Netflex\Pages\Contracts\MediaUrlResolvable;

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
use Netflex\Foundation\Wallet\Contracts\PKPassRepresentable;
use Symfony\Component\HttpFoundation\HeaderBag;
use TypeError;

class PKPass implements Responsable, JsonSerializable, Jsonable, PKPassRepresentable
{
    const FORMAT_QR = 'PKBarcodeFormatQR';
    const FORMAT_PDF417 = 'PKBarcodeFormatPDF417';
    const FORMAT_AZTEC = 'PKBarcodeFormatAztec';
    const FORMAT_CODE128 = 'PKBarcodeFormatCode128';

    const TYPE_GENERIC = 'generic';
    const TYPE_BOARDING_PASS = 'boardingPass';
    const TYPE_STORE_CARD = 'storeCard';
    const TYPE_EVENT_TICKET = 'eventTicket';
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
    protected $i18n = [];

    protected $fields = [
        'headerFields' => [],
        'primaryFields' => [],
        'secondaryFields' => [],
        'auxiliaryFields' => [],
        'backFields' => [],
    ];

    protected function __construct(string $type = PKPass::TYPE_GENERIC, ?array $data = [], ?array $fields = [])
    {
        $this->type = $type;

        $this->data = array_merge([
            'formatVersion' => 1,
        ], $data ?? []);

        $this->fields = $fields ?? [
            'headerFields' => [],
            'primaryFields' => [],
            'secondaryFields' => [],
            'auxiliaryFields' => [],
            'backFields' => [],
        ];
    }

    /**
     * Creates a boarding pass
     * 
     * @param array|null $data
     * @param array|null $fields
     * @return static
     */
    public static function boardingPass(?array $data = null, ?array $fields = null)
    {
        return new static(self::TYPE_BOARDING_PASS, $data, $fields);
    }

    /**
     * Creates a store card
     * 
     * @param array|null $data
     * @param array|null $fields
     * @return static
     * */
    public static function storeCard(?array $data = null, ?array $fields = null)
    {
        return new static(self::TYPE_STORE_CARD, $data, $fields);
    }

    /**
     * Creates an event ticket
     * 
     * @param array|null $data
     * @param array|null $fields
     * @return static
     * */
    public static function eventTicket(?array $data = null, ?array $fields = null)
    {
        return new static(self::TYPE_EVENT_TICKET, $data, $fields);
    }

    /**
     * Creates a coupon
     * 
     * @return static
     * */
    public static function coupon(?array $data = null, ?array $fields = null)
    {
        return new static(self::TYPE_COUPON, $data, $fields);
    }

    /**
     * Creates a generic pass
     * 
     * @param array|null $data
     * @param array|null $fields
     * @return static
     * */
    public static function generic(?array $data = null, ?array $fields = null)
    {
        return new static(self::TYPE_GENERIC);
    }

    /**
     * A URL to be passed to the associated app when launching it.
     *
     * @param integer $storeIdentifier
     * @return static
     */
    public function associatedStoreIdentifier(int $storeIdentifier)
    {
        $this->data['associatedStoreIdentifiers'] = $this->data['associatedStoreIdentifiers'] ?? [];
        $this->data['associatedStoreIdentifiers'][] = [$storeIdentifier];
        return $this;
    }

    public function appLaunchURL(string $appLaunchURL)
    {
        $this->data['appLaunchURL'] = $appLaunchURL;
        return $this;
    }

    /**
     * Custom information for companion apps. This data is not displayed to the user.
     *
     * @param mixed $userInfo
     * @return static
     */
    public function userInfo($userInfo)
    {
        $this->data['userInfo'] = $userInfo;
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

    public function transitType(string $transitType)
    {
        if ($this->type === static::TYPE_BOARDING_PASS) {
            if (in_array($transitType, [self::TRANSIT_TYPE_AIR, self::TRANSIT_TYPE_BOAT, self::TRANSIT_TYPE_BUS, self::TRANSIT_TYPE_GENERIC, self::TRANSIT_TYPE_TRAIN])) {
                $this->fields['transitType'] = $transitType;
            }

            return $this;
        }

        throw new InvalidArgumentException('This pass type does not support transit type');
    }

    public function addLocale(string $locale, array $messages)
    {
        $this->i18n[$locale] = $messages;
        return $this;
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

    /**
     * Marks the pass as voided.
     *
     * @param boolean $voided
     * @return static
     */
    public function voided(bool $voided = true)
    {
        $this->data['voided'] = $voided;
        return $this;
    }

    /**
     * Prevent the user from sharing the pass
     *
     * @param boolean $sharingProhibited
     * @return static
     */
    public function sharingProhibited(bool $sharingProhibited = true)
    {
        $this->data['sharingProhibited'] = $sharingProhibited;
        return $this;
    }

    /**
     * Serial number that uniquely identifies the pass. No two passes with the same pass type identifier may have the same serial number.
     *
     * @param string $serialNumber
     * @return static
     */
    public function serialNumber(string $serialNumber)
    {
        $this->data['serialNumber'] = $serialNumber;
        return $this;
    }

    /**
     * Brief description of the pass, used by the iOS accessibility technologies.
     *
     * @param string $description
     * @return static
     */
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
     * @param string|null $relevantText
     * @return static
     */
    public function addLocation(float $latitude, float $longitude, ?string $relevantText = null)
    {
        $this->data['locations'] = $this->data['locations'] ?? [];

        $location = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($relevantText) {
            $location['relevantText'] = $relevantText;
        }

        $this->data['locations'][] = $location;

        return $this;
    }

    /**
     * Add a BLE proximity beacon for which to automatiocally suggest to display the pass when in range
     *
     * @param string $proximityUUID
     * @param integer|null $major
     * @param integer|null $minor
     * @param string|null $relevantText
     * @return static
     */
    public function addBeacon(string $proximityUUID, ?int $major = null, ?int $minor = null, ?string $relevantText = null)
    {
        $this->data['beacons'] = $this->data['beacons'] ?? [];

        $beacon = [
            'proximityUUID' => $proximityUUID,
        ];

        if ($major) {
            $beacon['major'] = $major;
        }

        if ($minor) {
            $beacon['minor'] = $minor;
        }

        if ($relevantText) {
            $beacon['relevantText'] = $relevantText;
        }

        $this->data['beacons'][] = $beacon;

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
        $this->data['barcodes'] = $this->data['barcodes'] ?? [];

        $this->data['barcodes'][] = [
            'format' => $format,
            'message' => $barcode,
            'altText' => $altText ?? $barcode,
            'messageEncoding' => 'iso-8859-1',
        ];

        return $this;
    }

    /**
     * @param string $webServiceURL See https://developer.apple.com/library/archive/documentation/PassKit/Reference/PassKit_WebService/WebService.html#//apple_ref/doc/uid/TP40011988
     * @param string $authenticationToken The token must be 16 characters or longer.
     * @return static
     * @throws InvalidArgumentException
     */
    public function webService(string $webServiceURL, string $authenticationToken)
    {
        if (strlen($authenticationToken) < 16) {
            throw new InvalidArgumentException('Authentication token must be 16 characters or longer');
        }

        $this->data['webServiceURL'] = $webServiceURL;
        $this->data['authenticationToken'] = $authenticationToken;

        return $this;
    }

    /**
     * Identifier used to group related passes.
     * Optional for event tickets and boarding passes; otherwise not allowed. 
     *
     * @param string $groupingIdentifier
     * @return static
     * @throws InvalidArgumentException
     */
    public function groupingIdentifier(string $groupingIdentifier)
    {
        if (in_array($this->type, [static::TYPE_BOARDING_PASS, static::TYPE_EVENT_TICKET])) {
            $this->data['groupingIdentifier'] = $groupingIdentifier;
            return $this;
        }

        throw new InvalidArgumentException('Grouping identifier is only supported for boarding passes and event tickets');
    }

    /**
     * @param UploadedFile|File|MediaUrlResolvable|string $file A file or a path to a file
     * @param string|null $name
     * @return array
     */
    protected function encodeFile($file, ?string $name = null)
    {
        if (($file instanceof UploadedFile) || ($file instanceof File)) {
            if (!$name) {
                $name = $file->getClientOriginalName();
            }

            $path = 'data:text/plain;base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        if ($file instanceof MediaUrlResolvable) {
            $path = $file->url();
            if (!$name) {
                $name = basename($path);
            }
        }

        if (is_string($file)) {
            $path = $file;

            if (!$name) {
                $name = basename($path);
            }

            if (!Str::startsWith($path, 'http')) {
                $path = 'data:text/plain;base64,' . base64_encode(file_get_contents($file));
            }
        }

        return ['name' => $name, 'path' => $path];;
    }

    /**
     * Add background image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addBackground($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'background.png');
        }

        return $this->addFile($file, 'background.png');
    }

    /**
     * Add thumbnail image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addThumbnail($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'thumbnail.png');
        }

        return $this->addFile($file, 'thumbnail.png');
    }

    /**
     * Add logo image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addLogo($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'logo.png');
        }

        return $this->addFile($file, 'logo.png');
    }

    /**
     * Add strip image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addStrip($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'strip.png');
        }

        return $this->addFile($file, 'strip.png');
    }

    /**
     * Add footer image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addFooter($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'footer.png');
        }

        return $this->addFile($file, 'footer.png');
    }

    /**
     * Add icon image to the pass
     *
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $locale
     * @return static
     */
    public function addIcon($file, ?string $locale = null)
    {
        if ($locale) {
            return $this->addLocalizedFile($locale, $file, 'icon.png');
        }

        return $this->addFile($file, 'icon.png');
    }

    /**
     * @param string $locale
     * @param UploadedFile|File|MediaUrlResolvable|string $file
     * @param string|null $name
     * @return static
     */
    public function addLocalizedFile(string $locale, $file, ?string $name = null)
    {
        $payload = $this->encodeFile($file, $name);
        $payload['locale'] = $locale;
        $this->files[] = $payload;

        return $this;
    }

    /**
     * @param UploadedFile|File|MediaUrlResolvable|string $file A file or a path to a file
     * @param string|null $name
     * @param string|null $locale
     * @return static
     */
    public function addFile($file, ?string $name = null, $locale = null)
    {
        $this->files[] = $this->encodeFile($file, $name);

        return $this;
    }

    /**
     * Gets the signed pass as a binary blob
     *
     * @return mixed
     */
    public function blob()
    {
        return $this->download()->content();
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
                    'files' => $this->files,
                    'i18n' => $this->i18n,
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

    /**
     * Return the Apple Wallet Pass schema
     *
     * @return object
     */
    public function schema()
    {
        return once(function () {
            $schema = file_get_contents(__DIR__ . '/PKPass.schema.json');
            return json_decode($schema);
        });
    }

    public function validate()
    {
        $validator = new Validator;
        $data = $this->payload();
        $data = json_decode(json_encode($data));
        $validator->validate($data, $this->schema(), Constraint::CHECK_MODE_COERCE_TYPES);

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

    public function toPKPass(): PKPass
    {
        return $this;
    }
}

$pkpass = PKPass::boardingPass();
