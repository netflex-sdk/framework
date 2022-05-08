<?php

namespace Netflex\Customers;

use JsonSerializable;

use Netflex\Customers\Consent;
use Netflex\Customers\Customer;

use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Request;

use Netflex\API\Facades\API;
use Netflex\Support\Accessors;

/**
 * @property int $assignment_id
 * @property Consent $consent
 * @property boolean $active
 * @property Carbon $timestamp
 * @property Carbon|null $revoked_timestamp
 * @property string|null $source
 * @property string|null $ip
 * @property string|null $comment
 */
class ConsentAssignment implements JsonSerializable, Jsonable
{
    use Accessors;

    /** @var int */
    protected $customer_id;

    /**
     * @param array $attributes
     * @param int $customer_id
     */
    protected function __construct($attributes = [], $customer_id = null)
    {
        $this->attributes = $attributes;
        $this->custoner_id = $customer_id;
    }

    /**
     * @return int
     */
    public function getIdAttribute()
    {
        return $this->assignment_id;
    }

    /**
     * Undocumented function
     *
     * @param array $consent
     * @return Consent
     */
    public function getConsentAttribute($consent)
    {
        return Consent::newFromBuilder($consent);
    }

    /**
     * @param string $assignment_id
     * @return int
     */
    public function getAssignmentIdAttribute($assignment_id)
    {
        return (int) $assignment_id;
    }

    /**
     * @param string $active
     * @return boolean
     */
    public function getActiveAttribute($active)
    {
        return (bool) $active;
    }

    /**
     * @param string $timestamp
     * @return Carbon
     */
    public function getTimestampAttribute($timestamp)
    {
        return Carbon::parse($timestamp);
    }

    /**
     * @param string $revoked_timestamp
     * @return Carbon|null
     */
    public function getRevokedTimestampAttribute($revoked_timestamp)
    {
        if ($revoked_timestamp) {
            return Carbon::parse($revoked_timestamp);
        }
    }

    /**
     * Revokes the assignment
     *
     * @param Carbon|string|null $timestamp
     * @return boolean
     */
    public function revoke($timestamp = null)
    {
        API::put('relations/consents/assignment/revoke/' . $this->assignment_id, [
            'revoke_timestamp' => $timestamp ? (($timestamp instanceof Carbon) ? $timestamp->toDateTimeString() : $timestamp) : null
        ]);

        return true;
    }

    /**
     * Creates a assignment
     *
     * @param Consent|int $consent
     * @param Customer|int $customer
     * @param array $options
     * @return int Assignment id
     */
    public static function create($consent, $customer, $options = [])
    {
        $timestamp = null;
        $ip = null;

        if (isset($options['timestamp'])) {
            $timestamp = ($options['timestamp'] instanceof Carbon) ? $options['timestamp']->toDateTimeString() : $options['timestamp'];
        }

        if (array_key_exists('ip', $options)) {
            $ip = $ip;
        } else {
            $ip = Request::ip();
        }

        $response = API::post('relations/consents/customer', [
            'customer_id' => ($customer instanceof Customer) ? $customer->id : $customer,
            'consent_id' => ($consent instanceof Consent) ? $consent->id : $consent,
            'source' => $options['source'] ?? null,
            'comment' => $options['comment'] ?? null,
            'timestamp' => $timestamp,
            'ip' => $ip
        ]);

        return $response->assignment_id;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public static function newFromBuilder($attributes)
    {
        $customer_id = null;

        if (isset($attributes['customer_id'])) {
            $customer_id = $attributes['customer_id'];
            unset($attributes['customer_id']);
        }

        return new static($attributes, $customer_id);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'assignment_id' => $this->assignment_id,
            'consent' => $this->consent,
            'active' => $this->active,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'revoked_timestamp' => $this->revoked_timestamp ? $this->revoked_timestamp->toDateTimeString() : null,
            'source' => $this->source,
            'ip' => $this->ip,
            'comment' => $this->comment
        ];
    }

    /**
     * @param integer $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }
}
