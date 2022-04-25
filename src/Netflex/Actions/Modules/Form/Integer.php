<?php

namespace Netflex\Actions\Modules\Form;

final class Integer extends BookingFormField
{
    private ?int $minQty = null;
    private ?int $maxQty = null;

    public function __construct(string $alias, ?string $label = "")
    {
        parent::__construct($alias, $label);
    }

    public static function create(string $alias, ?string $label = "", ?int $minQty = null, ?int $maxQty = null)
    {
        return new static($alias, $label, $minQty, $maxQty);
    }

    function getType(): string
    {
        return "integer";
    }

    function withRange(?int $min, ?int $max)
    {
        return $this->withMinValue($min)->withMaxValue($max);
    }

    function withMaxValue(?int $max): self
    {
        $this->maxQty = $max;
        return $this;
    }

    function withMinValue(?int $min): self
    {
        $this->minQty = $min;
        return $this;
    }

    public function toPayload(): array
    {
        $payload = parent::toPayload();
        if ($this->minQty !== null) {
            $payload['minQty'] = $this->minQty;
        }

        if ($this->maxQty !== null) {
            $payload['maxQty'] = $this->maxQty;
        }

        return $payload;
    }
}
