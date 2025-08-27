<?php

namespace Netflex\Customers;

use Carbon\Month;
use Carbon\WeekDay;
use DateTimeInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonSerializable;

use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Request;

use Netflex\API\Facades\API;
use Netflex\Support\Accessors;
use ReturnTypeWillChange;

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

    protected ?int $customer_id;

    /**
     * @param array $attributes
     * @param int|null $customer_id
     */
    protected function __construct(
        array $attributes = [],
        int|null $customer_id = null,
    ) {
        $this->attributes = $attributes;
        $this->customer_id = $customer_id;
    }

    /**
     * @return int
     */
    public function getIdAttribute(): int
    {
        return $this->assignment_id;
    }

    /**
     * Undocumented function
     *
     * @param array $consent
     * @return Consent
     */
    public function getConsentAttribute(
        array $consent,
    ): Consent {
        return Consent::newFromBuilder($consent);
    }

    /**
     * @param string $assignment_id
     * @return int
     */
    public function getAssignmentIdAttribute(string $assignment_id): int
    {
        return (int) $assignment_id;
    }

    /**
     * @param string $active
     * @return boolean
     */
    public function getActiveAttribute(string $active): bool
    {
        return (bool) $active;
    }

    /**
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $timestamp
     * @return Carbon
     */
    public function getTimestampAttribute(
        DateTimeInterface|WeekDay|Month|string|int|float|null $timestamp,
    ): Carbon {
        return Carbon::parse($timestamp);
    }

    /**
     * @param string|null $revoked_timestamp
     * @return Carbon|null
     */
    public function getRevokedTimestampAttribute(?string $revoked_timestamp): ?Carbon
    {
        if ($revoked_timestamp) {
            return Carbon::parse($revoked_timestamp);
        }

        return null;
    }

    /**
     * Revokes the assignment
     *
     * @param string|Carbon|null $timestamp
     * @return boolean
     * @throws GuzzleException
     */
    public function revoke(Carbon|string|null $timestamp = null): bool
    {
        API::put(
            'relations/consents/assignment/revoke/' . $this->assignment_id,
            [
                'revoke_timestamp' => $timestamp
                    ? (($timestamp instanceof Carbon)
                        ? $timestamp->toDateTimeString() : $timestamp) : null,
            ],
        );

        return true;
    }

    /**
     * Creates a assignment
     *
     * @param int|Consent $consent
     * @param int|Customer $customer
     * @param array $options
     * @return int Assignment id
     * @throws GuzzleException
     */
    public static function create(
        Consent|int $consent,
        int|\Netflex\Customers\Customer $customer,
        array $options = [],
    ): int {
        $timestamp = null;

        if (isset($options['timestamp'])) {
            $timestamp = ($options['timestamp'] instanceof Carbon)
                ? $options['timestamp']->toDateTimeString()
                : $options['timestamp'];
        }

        if (array_key_exists('ip', $options)) {
            $ip = $options['ip'];
        } else {
            $ip = Request::ip();
        }

        $response = API::post('relations/consents/customer', [
            'customer_id' => ($customer instanceof Customer) ? $customer->id
                : $customer,
            'consent_id' => ($consent instanceof Consent) ? $consent->id
                : $consent,
            'source' => $options['source'] ?? null,
            'comment' => $options['comment'] ?? null,
            'timestamp' => $timestamp,
            'ip' => $ip,
        ]);

        return $response->assignment_id;
    }

    /**
     * @param array $attributes
     * @return static
     */
    public static function newFromBuilder(array $attributes): static
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
    #[ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [
            'assignment_id' => $this->assignment_id,
            'consent' => $this->consent,
            'active' => $this->active,
            'timestamp' => $this->timestamp->toDateTimeString(),
            'revoked_timestamp' => $this->revoked_timestamp
                ? $this->revoked_timestamp->toDateTimeString()
                : null,
            'source' => $this->source,
            'ip' => $this->ip,
            'comment' => $this->comment,
        ];
    }

    /**
     * @param integer $options
     * @return string
     */
    public function toJson($options = 0): string
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
