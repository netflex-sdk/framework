<?php

namespace Netflex\Customers;

use Exception;
use JsonSerializable;

use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Netflex\API\Facades\API;
use Netflex\Support\Accessors;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property Carbon $created
 * @property string|null $terms
 * @property string|null $terms_link
 * @property boolean $is_public
 * @property string|null $public_name
 * @property string|null $public_description
 */
class Consent implements JsonSerializable, Jsonable
{
    use Accessors;

    /**
     * @param array $attributes
     */
    protected function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * @param string $id
     * @return int
     */
    public function getIdAttribute($id)
    {
        return (int) $id;
    }

    /**
     * @param string $created
     * @return Carbon
     */
    public function getCreatedAttribute($created)
    {
        return Carbon::parse($created);
    }

    /**
     * @param string $is_public
     * @return boolean
     */
    public function getIsPublicAttribute($is_public)
    {
        return (bool) $is_public;
    }

    /**
     * @return static[]
     */
    public static function all()
    {
        return collect(API::get('relations/consents', true)['data'])
            ->map(function ($attributes) {
                return static::newFromBuilder($attributes);
            });
    }

    /**
     * @param int $id
     * @return static|null
     */
    public static function find($id)
    {
        try {
            if ($attributes = API::get('relations/consents/' . $id, true)) {
                return static::newFromBuilder($attributes);
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param int $id
     * @return static
     * @throws ModelNotFoundException
     */
    public static function findOrFail($id)
    {
        if ($consent = static::find($id)) {
            return $consent;
        }

        throw new ModelNotFoundException('Consent not found');
    }

    /**
     * @param array $attributes
     * @return static
     */
    public static function newFromBuilder($attributes)
    {
        return new static($attributes);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'created' => $this->created->toDateTimeString(),
            'terms' => $this->terms,
            'terms_link' => $this->terms_link,
            'is_public' => $this->is_public,
            'public_name' => $this->public_name,
            'public_description' => $this->public_description
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
