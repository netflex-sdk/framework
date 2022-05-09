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

    const PKPASS_SCHEMA =
    [
        '$schema' => 'http://json-schema.org/draft-04/schema#',
        'title' => 'Pass',
        'description' => 'Apple Wallet pass with localizations, NFC and web service push updates support.',
        'type' => 'object',
        'definitions' => [
            'w3cDate' => [
                'title' => 'ISO 8601 Date',
                'description' => 'An ISO 8601 formatted date represented as a JSON string.',
                'type' => 'string',
                'pattern' => '^20[1-9][2]-[01]\\d-[0-3]\\dT[0-5]\\d:[0-5]\\d(:[0-5]\\d)?(Z|([+-][01]\\d:[03]0)$)'
            ],
            'color' => [
                'title' => 'Color',
                'description' => 'CSS-style RGB triple color.',
                'type' => 'string',
                'pattern' => '^rgba?\((?: +)?(\d+)(?: +)?,(?: +)?\s*(\d+)(?: +)?,(?: +)?\s*(\d+)(?:(?: +)?,(?: +)?\s*(\d+(?:\.\d+)?))?(?: +)?\)$'
            ],
            'currencyCode' => [
                'title' => 'Currency Code',
                'description' => 'ISO 4217 currency code.',
                'type' => 'string',
                'pattern' => '^[A-Z][3,3]$'
            ],
            'currencyAmount' => [
                'title' => 'Currency Amount',
                'description' => 'An ISO 4217 currency code and an amount.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'currencyCode' => [
                        '$ref' => '#/definitions/currencyCode'
                    ],
                    'amount' => [
                        'title' => 'Amount',
                        'type' => 'string'
                    ]
                ]
            ],
            'personNameComponents' => [
                'title' => 'Person Name Components',
                'description' => 'An object that manages the separate parts of a person\'s name',
                'type' => 'object',
                'properties' => [
                    'namePrefix' => [
                        'title' => 'Name Prefix',
                        'description' => 'The portion of a name’s full form of address that precedes the name itself.',
                        'type' => 'string',
                        'examples' => [
                            'Dr.',
                            'Mr.',
                            'Ms.'
                        ]
                    ],
                    'givenName' => [
                        'title' => 'Given Name',
                        'description' => 'Name bestowed upon an individual to differentiate them from other members of a group that share a family name.',
                        'type' => 'string',
                        'examples' => [
                            'Johnathan'
                        ]
                    ],
                    'middleName' => [
                        'title' => 'Middle Name',
                        'description' => 'Secondary name bestowed upon an individual to differentiate them from others that have the same given name.',
                        'type' => 'string',
                        'examples' => [
                            'Maple'
                        ]
                    ],
                    'familyName' => [
                        'title' => 'Family Name',
                        'description' => 'Name bestowed upon an individual to denote membership in a group or family.',
                        'type' => 'string',
                        'examples' => [
                            'Appleseed'
                        ]
                    ],
                    'nameSuffix' => [
                        'title' => 'Name Suffix',
                        'description' => 'The portion of a name’s full form of address that follows the name itself.',
                        'type' => 'string',
                        'examples' => [
                            'Esq.',
                            'Jr.',
                            'Ph.D.'
                        ]
                    ],
                    'nickname' => [
                        'title' => 'Nickname',
                        'description' => 'Name substituted for the purposes of familiarity.',
                        'type' => 'string',
                        'examples' => [
                            'Johnny'
                        ]
                    ],
                    'phoneticRepresentation' => [
                        'title' => 'Phonetic Representation',
                        'description' => 'The phonetic representation name components of the receiver.',
                        'type' => 'object',
                        '$ref' => '#/definitions/personNameComponents',
                        'not' => [
                            'required' => [
                                'phoneticRepresentation'
                            ]
                        ]
                    ]
                ]
            ],
            'seat' => [
                'title' => 'Seat',
                'description' => 'A dictionary with seat information',
                'type' => 'object',
                'properties' => [
                    'seatSection' => [
                        'type' => 'string'
                    ],
                    'seatRow' => [
                        'type' => 'string'
                    ],
                    'seatNumber' => [
                        'type' => 'string'
                    ],
                    'seatIdentifier' => [
                        'type' => 'string'
                    ],
                    'seatType' => [
                        'type' => 'string'
                    ],
                    'seatDescription' => [
                        'type' => 'string'
                    ]
                ]
            ],
            'field' => [
                'title' => 'Field',
                'description' => 'Keys that define an individual field.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'attributedValue' => [
                        'title' => 'Attributed Value',
                        'description' => 'Attributed value of the field.\nThe value may contain HTML markup for links. Only the <a> tag and its href attribute are supported. This key’s value overrides the text specified by the value key.\nAvailable in iOS 7.0.',
                        'anyOf' => [
                            [
                                'type' => 'number'
                            ],
                            [
                                'type' => 'string'
                            ],
                            [
                                '$ref' => '#/definitions/w3cDate'
                            ]
                        ],
                        'examples' => [
                            '<a href="http://example.com/customers/123">Edit my profile</a>'
                        ]
                    ],
                    'changeMessage' => [
                        'title' => 'Change Message',
                        'description' => 'Format string for the alert text that is displayed when the pass is updated. The format string must contain the escape %@, which is replaced with the field’s new value. If you don’t specify a change message, the user isn’t notified when the field changes.\nLocalizable.',
                        'type' => 'string',
                        'examples' => [
                            'Gate changed to %@.'
                        ]
                    ],
                    'dataDetectorTypes' => [
                        'title' => 'Data Detector Types',
                        'description' => 'Data detectors that are applied to the field’s value. Provide an empty array to use no data detectors. Data detectors are applied only to back fields.',
                        'type' => 'array',
                        'uniqueItems' => true,
                        'items' => [
                            'anyOf' => [
                                [
                                    'const' => 'PKDataDetectorTypePhoneNumber'
                                ],
                                [
                                    'const' => 'PKDataDetectorTypeLink'
                                ],
                                [
                                    'const' => 'PKDataDetectorTypeAddress'
                                ],
                                [
                                    'const' => 'PKDataDetectorTypeCalendarEvent'
                                ]
                            ]
                        ],
                        'default' => [
                            'PKDataDetectorTypePhoneNumber',
                            'PKDataDetectorTypeLink',
                            'PKDataDetectorTypeAddress',
                            'PKDataDetectorTypeCalendarEvent'
                        ]
                    ],
                    'key' => [
                        'title' => 'Key',
                        'description' => 'The key must be unique within the scope of the entire pass.',
                        'type' => 'string',
                        'examples' => [
                            'departure-gate.'
                        ]
                    ],
                    'label' => [
                        'title' => 'Label',
                        'description' => 'Label text for the field.\nLocalizable.',
                        'type' => 'string'
                    ],
                    'textAlignment' => [
                        'title' => 'Text Alignment',
                        'description' => 'Alignment for the field’s contents.\nThis key is not allowed for primary fields or back fields.',
                        'type' => 'string',
                        'enum' => [
                            'PKTextAlignmentLeft',
                            'PKTextAlignmentCenter',
                            'PKTextAlignmentRight',
                            'PKTextAlignmentNatural'
                        ],
                        'default' => 'PKTextAlignmentNatural'
                    ],
                    'value' => [
                        'title' => 'Value',
                        'description' => 'Value of the field',
                        'anyOf' => [
                            [
                                'type' => 'number'
                            ],
                            [
                                'type' => 'string'
                            ],
                            [
                                '$ref' => '#/definitions/w3cDate'
                            ]
                        ],
                        'examples' => [
                            42
                        ]
                    ],
                    'dateStyle' => [
                        'title' => 'Date Style',
                        'description' => 'Style of date to display.',
                        'type' => 'string',
                        'enum' => [
                            'PKDateStyleNone',
                            'PKDateStyleShort',
                            'PKDateStyleMedium',
                            'PKDateStyleLong',
                            'PKDateStyleFull'
                        ]
                    ],
                    'ignoresTimeZone' => [
                        'title' => 'Ignores Time Zone',
                        'description' => 'Always display the time and date in the given time zone, not in the user’s current time zone.\nThe format for a date and time always requires a time zone, even if it will be ignored. For backward compatibility with iOS 6, provide an appropriate time zone, so that the information is displayed meaningfully even without ignoring time zones.\nThis key does not affect how relevance is calculated.\nAvailable in iOS 7.0.',
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'isRelative' => [
                        'title' => 'Is Relative',
                        'description' => 'If true, the label’s value is displayed as a relative date; otherwise, it is displayed as an absolute date.\nThis key does not affect how relevance is calculated.',
                        'type' => 'boolean',
                        'default' => false
                    ],
                    'timeStyle' => [
                        'title' => 'Time Style',
                        'description' => 'Style of time to display.',
                        'type' => 'string',
                        'enum' => [
                            'PKDateStyleNone',
                            'PKDateStyleShort',
                            'PKDateStyleMedium',
                            'PKDateStyleLong',
                            'PKDateStyleFull'
                        ]
                    ],
                    'currencyCode' => [
                        '$ref' => '#/definitions/currencyCode'
                    ],
                    'numberStyle' => [
                        'title' => 'Number Style',
                        'description' => 'Style of number to display. Number styles have the same meaning as the Cocoa number formatter styles with corresponding names. See https://developer.apple.com/documentation/foundation/nsnumberformatterstyle',
                        'type' => 'string',
                        'enum' => [
                            'PKNumberStyleDecimal',
                            'PKNumberStylePercent',
                            'PKNumberStyleScientific',
                            'PKNumberStyleSpellOut'
                        ]
                    ],
                    'semantics' => [
                        'title' => 'Semantics',
                        'description' => 'Machine-readable metadata to allow the system to offer Wallet passes to users intelligently.',
                        '$comment' => 'All semantic data is optional and different semantic fields can be placed in auxiliary, primary, secondary, header or back fields.',
                        'type' => 'object',
                        'properties' => [
                            'totalPrice' => [
                                'title' => 'Total Price',
                                'description' => 'The total price for the pass.',
                                '$ref' => '#/definitions/currencyAmount'
                            ],
                            'duration' => [
                                'title' => 'Duration',
                                'description' => 'The duration of the event or transit journey, in seconds.',
                                'type' => 'number'
                            ],
                            'seats' => [
                                'title' => 'Seats',
                                'description' => 'Seating details for all seats at the event or transit journey.',
                                'type' => 'array',
                                'items' => [
                                    '$ref' => '#/definitions/seat'
                                ]
                            ],
                            'silenceRequested' => [
                                'title' => 'Silence Requested',
                                'description' => 'Request the user\'s device to remain silent during a the event or transit journey. This key may not be honored and the system will determine the length of the silence period.',
                                'type' => 'boolean'
                            ],
                            'departureLocation' => [
                                'title' => 'Departure Location',
                                'description' => 'The geographic coordinates of the transit departure, suitable to be shown on a map. If possible, precise locations are more useful to travelers, such as the specific location of the gate at an airport.',
                                '$ref' => '#/definitions/location'
                            ],
                            'departureLocationDescription' => [
                                'title' => 'Departure Location Description',
                                'description' => 'A brief description of the departure location.',
                                'type' => 'string',
                                'examples' => [
                                    'San Francisco'
                                ]
                            ],
                            'destinationLocation' => [
                                'title' => 'Destination Location',
                                'description' => 'The geographic coordinates of the transit destination, suitable to be shown on a map.',
                                '$ref' => '#/definitions/location'
                            ],
                            'destinationLocationDescription' => [
                                'title' => 'Destination Location Description',
                                'description' => 'A brief description of the destination location.',
                                '$ref' => '#/definitions/location'
                            ],
                            'transitProvider' => [
                                'title' => 'Transit Provider',
                                'description' => 'The name of the transit company.',
                                'type' => 'string'
                            ],
                            'vehicleName' => [
                                'title' => 'Vehicle Name',
                                'description' => 'The name of the vehicle being boarded, such as the name of a boat.',
                                'type' => 'string'
                            ],
                            'vehicleNumber' => [
                                'title' => 'Vehicle Number',
                                'description' => 'The identifier of the vehicle being boarded, such as the aircraft registration number or train number.',
                                'type' => 'string'
                            ],
                            'vehicleType' => [
                                'title' => 'Vehicle Type',
                                'description' => 'A brief description of the type of vehicle being boarded, such as the model and manufacturer of a plane or the class of a boat.',
                                'type' => 'string'
                            ],
                            'originalDepartureDate' => [
                                'title' => 'Original Departure Date',
                                'description' => 'The original scheduled date and time of departure.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'currentDepartureDate' => [
                                'title' => 'Current Departure Date',
                                'description' => 'The updated date and time of departure, if different than the original scheduled date.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'originalArrivalDate' => [
                                'title' => 'Original Arrival Date',
                                'description' => 'The original scheduled date and time of arrival.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'currentArrivalDate' => [
                                'title' => 'Current Arrival Date',
                                'description' => 'The updated date and time of arrival, if different than the original scheduled date.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'originalBoardingDate' => [
                                'title' => 'Original Boarding Date',
                                'description' => 'The original scheduled date and time of boarding.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'currentBoardingDate' => [
                                'title' => 'Current Boarding Date',
                                'description' => 'The updated date and time of boarding, if different than the original scheduled date.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'boardingGroup' => [
                                'title' => 'Boarding Group',
                                'description' => 'A group number for boarding.',
                                'type' => 'string'
                            ],
                            'boardingSequenceNumber' => [
                                'title' => 'Boarding Sequence Number',
                                'description' => 'A sequence number for boarding.',
                                'type' => 'string'
                            ],
                            'confirmationNumber' => [
                                'title' => 'Confirmation Number',
                                'description' => 'A booking or reservation confirmation number.',
                                'type' => 'string'
                            ],
                            'transitStatus' => [
                                'title' => 'Transit Status',
                                'description' => 'A brief description of the current status of the vessel being boarded. For delayed statuses, provide currentBoardingDate, currentDepartureDate, and currentArrivalDate where available.',
                                'type' => 'string',
                                'examples' => [
                                    'On Time',
                                    'Delayed'
                                ]
                            ],
                            'transitStatusReason' => [
                                'title' => 'Transit Status Reason',
                                'description' => 'A brief description explaining the reason for the current transitStatus',
                                'type' => 'string',
                                'examples' => [
                                    'Thunderstorms'
                                ]
                            ],
                            'passengerName' => [
                                'title' => 'Passenger Name',
                                'description' => 'The passenger\'s name.',
                                '$ref' => '#/definitions/personNameComponents'
                            ],
                            'membershipProgramName' => [
                                'title' => 'Membership Program Name',
                                'description' => 'The name of a frequent flyer or loyalty program.',
                                'type' => 'string'
                            ],
                            'membershipProgramNumber' => [
                                'title' => 'Membership Program Number',
                                'description' => 'The ticketed passenger\'s frequent flyer or loyalty number.',
                                'type' => 'string'
                            ],
                            'priorityStatus' => [
                                'title' => 'Priority Status',
                                'description' => 'he priority status held by the ticketed passenger',
                                'type' => 'string',
                                'examples' => [
                                    'Gold',
                                    'Silver'
                                ]
                            ],
                            'securityScreening' => [
                                'title' => 'Security Screening',
                                'description' => ' The type of security screening that the ticketed passenger will be subject to, such as "Priority".',
                                'type' => 'string'
                            ],
                            'flightCode' => [
                                'title' => 'Flight Code',
                                'description' => 'The IATA flight code',
                                'type' => 'string',
                                'examples' => [
                                    'EX123'
                                ]
                            ],
                            'airlineCode' => [
                                'title' => 'The IATA airline code',
                                'description' => 'The IATA airline code, such as "EX" for flightCode "EX123".',
                                'type' => 'string',
                                'examples' => [
                                    'EX'
                                ]
                            ],
                            'flightNumber' => [
                                'title' => 'Flight Number',
                                'description' => 'The numeric portion of the IATA flightCode, such as 123 for flightCode "EX123"',
                                'type' => 'number',
                                'examples' => [
                                    123
                                ]
                            ],
                            'departureAirportCode' => [
                                'title' => 'Departure Airport Code',
                                'description' => 'The IATA airport code for the departure airport.',
                                'type' => 'string',
                                'examples' => [
                                    'SFO',
                                    'SJC'
                                ]
                            ],
                            'departureAirportName' => [
                                'title' => 'Departure Airport Name',
                                'description' => 'The full name of the departure airport',
                                'type' => 'string',
                                'examples' => [
                                    'San Francisco International Airport'
                                ]
                            ],
                            'departureTerminal' => [
                                'title' => 'Departure Terminal',
                                'description' => 'The terminal name or letter of the departure terminal, such as "A". Do not include the word "Terminal"',
                                'type' => 'string',
                                'examples' => [
                                    'A'
                                ]
                            ],
                            'departureGate' => [
                                'title' => 'Departure Gate',
                                'description' => 'The gate number or letters of the departure gate, such as "1A". Do not include the word "Gate".',
                                'type' => 'string',
                                'examples' => [
                                    '1A'
                                ]
                            ],
                            'destinationAirportCode' => [
                                'title' => 'Destination Airport Code',
                                'description' => 'The IATA airport code for the destination airport.',
                                'type' => 'string',
                                'examples' => [
                                    'SFO',
                                    'SJC'
                                ]
                            ],
                            'destinationAirportName' => [
                                'title' => 'Destination Airport Name',
                                'description' => 'The full name of the destination airport',
                                'type' => 'string',
                                'examples' => [
                                    'San Francisco International Airport'
                                ]
                            ],
                            'destinationTerminal' => [
                                'title' => 'Destination Terminal',
                                'description' => 'The terminal name or letter of the destination terminal, such as "A". Do not include the word "Terminal"',
                                'type' => 'string',
                                'examples' => [
                                    'A'
                                ]
                            ],
                            'destinationGate' => [
                                'title' => 'Destination Gate',
                                'description' => 'The gate number or letters of the destination gate, such as "1A". Do not include the word "Gate".',
                                'type' => 'string',
                                'examples' => [
                                    '1A'
                                ]
                            ],
                            'departurePlatform' => [
                                'title' => 'Departure Platform',
                                'description' => 'The name of the departure platform, such as "A". Do not include the word "Platform".',
                                'type' => 'string',
                                'examples' => [
                                    'A'
                                ]
                            ],
                            'departureStationName' => [
                                'title' => 'Departure Station Name',
                                'description' => 'The name of the departure station.',
                                'type' => 'string',
                                'examples' => [
                                    '1st Street Station'
                                ]
                            ],
                            'destinationPlatform' => [
                                'title' => 'Destination Platform',
                                'description' => 'The name of the destination platform, such as "A". Do not include the word "Platform".',
                                'type' => 'string',
                                'examples' => [
                                    'A'
                                ]
                            ],
                            'destinationStationName' => [
                                'title' => 'Destination Station Name',
                                'description' => 'The name of the destination station.',
                                'type' => 'string',
                                'examples' => [
                                    '1st Street Station'
                                ]
                            ],
                            'carNumber' => [
                                'title' => 'Car Number',
                                'description' => 'The car number.',
                                'type' => 'string'
                            ],
                            'eventName' => [
                                'title' => 'Event Name',
                                'description' => 'The full name for the event, such as the title of a movie.',
                                'type' => 'string'
                            ],
                            'venueName' => [
                                'title' => 'Venue Name',
                                'description' => 'The full name of the venue.',
                                'type' => 'string'
                            ],
                            'venueLocation' => [
                                'title' => 'Venue Location',
                                'description' => 'The geographic coordinates of the venue.',
                                '$ref' => '#/definitions/location'
                            ],
                            'venueEntrance' => [
                                'title' => 'Venue Entrance',
                                'description' => 'The full name of the entrance to use to gain access to the ticketed event.',
                                'type' => 'string',
                                'examples' => [
                                    'Gate A'
                                ]
                            ],
                            'venuePhoneNumber' => [
                                'title' => 'Venue Phone Number',
                                'description' => 'The phone number for enquiries about the venue\'s ticketed event.',
                                'type' => 'string'
                            ],
                            'venueRoom' => [
                                'title' => 'Venue Room',
                                'description' => 'The full name of the room where the ticketed event is taking place.',
                                'type' => 'string'
                            ],
                            'eventType' => [
                                'title' => 'Event Type',
                                'description' => 'The event type.',
                                'type' => 'string',
                                'enum' => [
                                    'PKEventTypeGeneric',
                                    'PKEventTypeLivePerformance',
                                    'PKEventTypeMovie',
                                    'PKEventTypeSports',
                                    'PKEventTypeConference',
                                    'PKEventTypeConvention',
                                    'PKEventTypeWorkshop',
                                    'PKEventTypeSocialGathering'
                                ]
                            ],
                            'eventStartDate' => [
                                'title' => 'Event Start Date',
                                'description' => 'The date and time the event starts.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'eventEndDate' => [
                                'title' => 'Event End Date',
                                'description' => 'The date and time the event ends.',
                                '$ref' => '#/definitions/w3cDate'
                            ],
                            'artistIDs' => [
                                'title' => 'Artist IDs',
                                'description' => 'The Adam IDs for the artists performing, in decreasing order of significance.',
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string'
                                ]
                            ],
                            'performerNames' => [
                                'title' => 'Performer Names',
                                'description' => 'The full names of the performers and opening acts, in decreasing order of significance.',
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string'
                                ]
                            ],
                            'genre' => [
                                'title' => 'Genre',
                                'description' => 'The genre of the performance.',
                                'type' => 'string'
                            ],
                            'leagueName' => [
                                'title' => 'League Name',
                                'description' => 'he unabbreviated league name for a sporting event.',
                                'type' => 'string'
                            ],
                            'leagueAbbreviation' => [
                                'title' => 'League Abbreviation',
                                'description' => 'The abbreviated league name for a sporting event.',
                                'type' => 'string'
                            ],
                            'homeTeamLocation' => [
                                'title' => 'Home Team Location',
                                'description' => 'The home location of the home team.',
                                'type' => 'string'
                            ],
                            'homeTeamName' => [
                                'title' => 'Home Team Name',
                                'description' => 'The name of the home team.',
                                'type' => 'string'
                            ],
                            'homeTeamAbbreviation' => [
                                'title' => 'Home Team Abbreviation',
                                'description' => 'The unique abbreviation of the home team\'s name.',
                                'type' => 'string'
                            ],
                            'awayTeamLocation' => [
                                'title' => 'Away Team Location',
                                'description' => 'The home location of the away team.',
                                'type' => 'string'
                            ],
                            'awayTeamName' => [
                                'title' => 'Away Team Name',
                                'description' => 'The name of the away team.',
                                'type' => 'string'
                            ],
                            'awayTeamAbbreviation' => [
                                'title' => 'Away Team Abbreviation',
                                'description' => 'The unique abbreviation of the away team\'s name.',
                                'type' => 'string'
                            ],
                            'sportName' => [
                                'title' => 'Sport Name',
                                'description' => 'The commonly used local name of the sport.',
                                'type' => 'string'
                            ],
                            'balance' => [
                                'title' => 'Balance',
                                'description' => 'The balance redeemable with the pass.',
                                '$ref' => '#/definitions/currencyAmount'
                            ]
                        ]
                    ]
                ],
                'required' => [
                    'key',
                    'value'
                ]
            ],
            'numericArray' => [
                'type' => 'array',
                'items' => [
                    'type' => 'number'
                ]
            ],
            'transitTypeNotRequired' => [
                'not' => [
                    'required' => [
                        'transitType'
                    ]
                ]
            ],
            'groupingIdentifierNotRequired' => [
                'not' => [
                    'required' => [
                        'groupingIdentifier'
                    ]
                ]
            ],
            'beacon' => [
                'title' => 'Beacon',
                'description' => 'Information about a location beacon.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'major' => [
                        'title' => 'Major',
                        'description' => 'Major identifier of a Bluetooth Low Energy location beacon.',
                        'type' => 'integer',
                        'minimum' => 0,
                        'maximum' => 65535,
                        '$comment' => '16-bit unsigned integer'
                    ],
                    'minor' => [
                        'title' => 'Minor',
                        'description' => 'Minor identifier of a Bluetooth Low Energy location beacon.',
                        'type' => 'integer',
                        'minimum' => 0,
                        'maximum' => 65535,
                        '$comment' => '16-bit unsigned integer'
                    ],
                    'proximityUUID' => [
                        'title' => 'Proximity UUID',
                        'description' => 'Unique identifier of a Bluetooth Low Energy location beacon.',
                        'type' => 'string'
                    ],
                    'relevantText' => [
                        'title' => 'Relevant Text',
                        'description' => 'Text displayed on the lock screen when the pass is currently relevant.',
                        'type' => 'string',
                        'examples' => [
                            'Store nearby on 1st and Main.'
                        ]
                    ]
                ],
                'required' => [
                    'proximityUUID'
                ]
            ],
            'location' => [
                'title' => 'Location',
                'description' => 'Information about a location.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'altitude' => [
                        'title' => 'Altitude',
                        'description' => 'Altitude, in meters, of the location.',
                        'type' => 'number'
                    ],
                    'latitude' => [
                        'title' => 'Latitude',
                        'description' => 'Latitude, in degrees, of the location.',
                        'type' => 'number',
                        'minimum' => -90,
                        'maximum' => 90
                    ],
                    'longitude' => [
                        'title' => 'Longitude',
                        'description' => 'Longitude, in degrees, of the location.',
                        'type' => 'number',
                        'minimum' => -180,
                        'maximum' => 180
                    ],
                    'relevantText' => [
                        'title' => 'Relevant Text',
                        'description' => 'Text displayed on the lock screen when the pass is currently relevant.',
                        'type' => 'string',
                        'examples' => [
                            'Store nearby on 1st and Main.'
                        ]
                    ]
                ],
                'required' => [
                    'latitude',
                    'longitude'
                ]
            ],
            'passStructure' => [
                'title' => 'Pass Structure',
                'description' => 'Keys that define the structure of the pass.\nThese keys are used for all pass styles and partition the fields into the various parts of the pass.',
                'type' => 'object',
                'properties' => [
                    'auxiliaryFields' => [
                        'title' => 'Auxiliary Fields',
                        'description' => 'Additional fields to be displayed on the front of the pass.',
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/definitions/field'
                        ]
                    ],
                    'backFields' => [
                        'title' => 'Back Fields',
                        'description' => 'Fields to be on the back of the pass.',
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/definitions/field'
                        ]
                    ],
                    'headerFields' => [
                        'title' => 'Header Fields',
                        'description' => 'Fields to be displayed in the header on the front of the pass.Use header fields sparingly; unlike all other fields, they remain visible when a stack of passes are displayed.',
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/definitions/field'
                        ]
                    ],
                    'primaryFields' => [
                        'title' => 'Primary Fields',
                        'description' => 'Fields to be displayed prominently on the front of the pass.',
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/definitions/field'
                        ]
                    ],
                    'secondaryFields' => [
                        'title' => 'Secondary Fields',
                        'description' => 'Fields to be displayed on the front of the pass.',
                        'type' => 'array',
                        'items' => [
                            '$ref' => '#/definitions/field'
                        ]
                    ],
                    'transitType' => [
                        'title' => 'Transit Type',
                        'description' => 'Type of transit.',
                        'type' => 'string',
                        'enum' => [
                            'PKTransitTypeAir',
                            'PKTransitTypeBoat',
                            'PKTransitTypeBus',
                            'PKTransitTypeGeneric',
                            'PKTransitTypeTrain'
                        ]
                    ]
                ]
            ],
            'barcode' => [
                'title' => 'Barcode',
                'description' => 'Information about a pass’s barcode.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'altText' => [
                        'title' => 'Alternative Text',
                        'description' => 'Text displayed near the barcode. For example, a human-readable version of the barcode data in case the barcode doesn’t scan.',
                        'type' => 'string'
                    ],
                    'format' => [
                        'title' => 'Format',
                        'description' => 'Barcode format. PKBarcodeFormatCode128 may only be used for dictionaries in the barcodes array.',
                        'type' => 'string',
                        'enum' => [
                            'PKBarcodeFormatQR',
                            'PKBarcodeFormatPDF417',
                            'PKBarcodeFormatAztec',
                            'PKBarcodeFormatCode128'
                        ]
                    ],
                    'message' => [
                        'title' => 'Message',
                        'description' => 'Message or payload to be displayed as a barcode.',
                        'type' => 'string'
                    ],
                    'messageEncoding' => [
                        'title' => 'Message Encoding',
                        'description' => 'Text encoding that is used to convert the message from the string representation to a data representation to render the barcode. The value is typically iso-8859-1, but you may use another encoding that is supported by your barcode scanning infrastructure.',
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'format',
                    'message',
                    'messageEncoding'
                ]
            ],
            'nfc' => [
                'title' => 'NFC',
                'description' => 'Information about the NFC payload passed to an Apple Pay terminal.',
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'message' => [
                        'title' => 'Message',
                        'description' => 'The payload to be transmitted to the Apple Pay terminal. Must be 64 bytes or less. Messages longer than 64 bytes are truncated by the system.',
                        'type' => 'string'
                    ],
                    'encryptionPublicKey' => [
                        'title' => 'Encryption Public Key',
                        'description' => ' The public encryption key used by the Value Added Services protocol. Use a Base64 encoded X.509 SubjectPublicKeyInfo structure containing a ECDH public key for group P256.',
                        'type' => 'string'
                    ]
                ],
                'required' => [
                    'message'
                ]
            ]
        ],
        'properties' => [
            'description' => [
                'title' => 'Description',
                'description' => 'Brief description of the pass, used by the iOS accessibility technologies.\nDon’t try to include all of the data on the pass in its description, just include enough detail to distinguish passes of the same type.\n Localizable.',
                'type' => 'string'
            ],
            'formatVersion' => [
                'title' => 'Format Version',
                'description' => 'Version of the file format.',
                'const' => 1
            ],
            'organizationName' => [
                'title' => 'Organization Name',
                'description' => 'Display name of the organization that originated and signed the pass.\n Localizable.',
                'type' => 'string'
            ],
            'serialNumber' => [
                'title' => 'Serial Number',
                'description' => 'Serial number that uniquely identifies the pass. No two passes with the same pass type identifier may have the same serial number.',
                'type' => 'string'
            ],
            'teamIdentifier' => [
                'title' => 'Team Identifier',
                'description' => 'Team identifier of the organization that originated and signed the pass, as issued by Apple.',
                'type' => 'string'
            ],
            'appLaunchURL' => [
                'title' => 'App Launch URL',
                'description' => 'A URL to be passed to the associated app when launching it. The app receives this URL in the application:didFinishLaunchingWithOptions: and application:openURL:options: methods of its app delegate.',
                'type' => 'string',
                'format' => 'iri'
            ],
            'associatedStoreIdentifiers' => [
                'title' => 'Associated Store Identifiers',
                'description' => 'A list of iTunes Store item identifiers for the associated apps.\nOnly one item in the list is used—the first item identifier for an app compatible with the current device. If the app is not installed, the link opens the App Store and shows the app. If the app is already installed, the link launches the app.',
                '$ref' => '#/definitions/numericArray'
            ],
            'userInfo' => [
                'title' => 'User Info',
                'description' => 'Custom information for companion apps. This data is not displayed to the user.\nFor example, a pass for a cafe could include information about the user’s favorite drink and sandwich in a machine-readable form for the companion app to read, making it easy to place an order for “the usual” from the app.\nAvailable in iOS 7.0.',
                'type' => 'object'
            ],
            'expirationDate' => [
                'title' => 'Expiration Date',
                'description' => 'Date and time when the pass expires.\nAvailable in iOS 7.0.',
                '$ref' => '#/definitions/w3cDate'
            ],
            'voided' => [
                'title' => 'Voided',
                'description' => 'Indicates that the pass is void—for example, a one time use coupon that has been redeemed.\nAvailable in iOS 7.0.',
                'type' => 'boolean',
                'default' => false
            ],
            'beacons' => [
                'title' => 'Beacons',
                'description' => 'Beacons marking locations where the pass is relevant.\nAvailable in iOS 7.0.',
                'type' => 'array',
                'items' => [
                    '$ref' => '#/definitions/beacon'
                ]
            ],
            'locations' => [
                'title' => 'Locations',
                'description' => 'Locations where the pass is relevant. For example, the location of your store.',
                'type' => 'array',
                'items' => [
                    '$ref' => '#/definitions/location'
                ]
            ],
            'maxDistance' => [
                'title' => 'Maximum Distance',
                'description' => 'Maximum distance in meters from a relevant latitude and longitude that the pass is relevant. This number is compared to the pass’s default distance and the smaller value is used.\nAvailable in iOS 7.0.',
                'type' => 'number'
            ],
            'relevantDate' => [
                'title' => 'Relevant Date',
                'description' => 'Date and time when the pass becomes relevant. For example, the start time of a movie.\nRecommended for event tickets and boarding passes.',
                '$ref' => '#/definitions/w3cDate'
            ],
            'boardingPass' => [
                'title' => 'Boarding Pass',
                'description' => 'Information specific to a boarding pass.',
                '$ref' => '#/definitions/passStructure',
                'required' => [
                    'transitType'
                ]
            ],
            'coupon' => [
                'title' => 'Coupon',
                'description' => 'Information specific to a coupon.',
                '$ref' => '#/definitions/passStructure',
                'anyOf' => [
                    [
                        '$ref' => '#/definitions/transitTypeNotRequired'
                    ]
                ]
            ],
            'eventTicket' => [
                'title' => 'Event Ticket',
                'description' => 'Information specific to an event ticket.',
                '$ref' => '#/definitions/passStructure',
                'anyOf' => [
                    [
                        '$ref' => '#/definitions/transitTypeNotRequired'
                    ]
                ]
            ],
            'generic' => [
                'title' => 'Generic Pass',
                'description' => 'Information specific to a generic pass.',
                '$ref' => '#/definitions/passStructure',
                'anyOf' => [
                    [
                        '$ref' => '#/definitions/transitTypeNotRequired'
                    ]
                ]
            ],
            'storeCard' => [
                'title' => 'Store Card',
                'description' => 'Information specific to a store card.',
                '$ref' => '#/definitions/passStructure',
                'anyOf' => [
                    [
                        '$ref' => '#/definitions/transitTypeNotRequired'
                    ]
                ]
            ],
            'barcode' => [
                'title' => 'Barcode',
                'description' => 'Information specific to the pass’s barcode.\nDeprecated in iOS 9.0 and later; use barcodes instead.',
                '$ref' => '#/definitions/barcode'
            ],
            'barcodes' => [
                'title' => 'Barcodes',
                'description' => 'Information specific to the pass’s barcode. The system uses the first valid barcode dictionary in the array. Additional dictionaries can be added as fallbacks.\nAvailable only in iOS 9.0 and later.',
                'type' => 'array',
                'items' => [
                    '$ref' => '#/definitions/barcode'
                ]
            ],
            'backgroundColor' => [
                'title' => 'Background Color',
                'description' => 'Background color of the pass, specified as an CSS-style RGB triple.',
                '$ref' => '#/definitions/color',
                'examples' => [
                    'rgb(23, 187, 82)'
                ]
            ],
            'foregroundColor' => [
                'title' => 'Foreground Color',
                'description' => 'Foreground color of the pass, specified as a CSS-style RGB triple',
                '$ref' => '#/definitions/color',
                'examples' => [
                    'rgb(100, 10, 110)'
                ]
            ],
            'groupingIdentifier' => [
                'title' => 'Grouping Identifier',
                'description' => 'Identifier used to group related passes. If a grouping identifier is specified, passes with the same style, pass type identifier, and grouping identifier are displayed as a group. Otherwise, passes are grouped automatically.\nUse this to group passes that are tightly related, such as the boarding passes for different connections of the same trip.\nAvailable in iOS 7.0.',
                'type' => 'string'
            ],
            'labelColor' => [
                'title' => 'Label Color',
                'description' => 'olor of the label text, specified as a CSS-style RGB triple.\nIf omitted, the label color is determined automatically.',
                '$ref' => '#/definitions/color',
                'examples' => [
                    'rgb(255, 255, 255)'
                ]
            ],
            'logoText' => [
                'title' => 'Logo Text',
                'description' => 'Text displayed next to the logo on the pass.\nLocalizable.',
                'type' => 'string'
            ],
            'suppressStripShine' => [
                'title' => 'Suppress Strip Shine',
                'description' => 'If true, the strip image is displayed without a shine effect. The default value prior to iOS 7.0 is false. In iOS 7.0, a shine effect is never applied, and this key is deprecated.',
                'type' => 'boolean',
                'default' => false
            ],
            'authenticationToken' => [
                'title' => 'Authentication Token',
                'description' => 'The authentication token to use with the web service.',
                'type' => 'string',
                'minLength' => 16
            ],
            'webServiceURL' => [
                'title' => 'Web Service URL',
                'description' => 'The URL of a web service that conforms to the API described in PassKit Web Service Reference. The web service must use the HTTPS protocol; the leading https:// is included in the value of this key. On devices configured for development, there is UI in Settings to allow HTTP web services.',
                'type' => 'string',
                'pattern' => '^https://'
            ],
            'nfc' => [
                'title' => 'NFC',
                'description' => 'Information used for Value Added Service Protocol transactions.\nAvailable in iOS 9.0.',
                '$ref' => '#/definitions/nfc'
            ]
        ],
        'dependencies' => [
            'appLaunchURL' => [
                'required' => [
                    'associatedStoreIdentifiers'
                ]
            ],
            'coupon' => [
                '$ref' => '#/definitions/groupingIdentifierNotRequired'
            ],
            'generic' => [
                '$ref' => '#/definitions/groupingIdentifierNotRequired'
            ],
            'storeCard' => [
                '$ref' => '#/definitions/groupingIdentifierNotRequired'
            ]
        ],
        'required' => [
            'description',
            'formatVersion',
            'organizationName',
            'serialNumber'
        ]
    ];

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
        $this->validate();

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
        if ($this->validate()) {
            return $this->payload();
        }
    }

    public function validate()
    {
        $validator = new Validator;
        $data = $this->payload();
        $data = json_decode(json_encode($data));
        $validator->validate($data, static::PKPASS_SCHEMA, Constraint::CHECK_MODE_COERCE_TYPES);

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
