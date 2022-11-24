<?php

namespace Netflex\Commerce;

use Illuminate\Contracts\Support\Arrayable;
use Netflex\Structure\Traits\Localizable;
use Netflex\Support\ReactiveObject;

class Properties extends ReactiveObject implements Arrayable
{
    protected $readOnlyAttributes = [];

    use Localizable;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $attributes = empty($this->attributes) ? [] : $this->attributes;

        foreach ($attributes as $key => $value) {
            if (!is_scalar($value)) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
