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

    function getType(): string
    {
        return "integer";
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

    public static function create(string $alias, ?string $label = "", ?int $minQty = null, ?int $maxQty = null)
    {
        return new static($alias, $label, $minQty, $maxQty);
    }
}
