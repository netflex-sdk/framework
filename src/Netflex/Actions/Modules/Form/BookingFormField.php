<?php

namespace Netflex\Actions\Modules\Form;

abstract class BookingFormField implements \JsonSerializable
{
    public string $alias;
    public string $label;
    public ?string $description = null;
    public ?string $value = null;

    public function __construct(string $alias, ?string $label = "")
    {
        $this->alias = $alias;
        $this->label = $label ?? "";
    }

    abstract function getType(): string;

    public function toPayload(): array
    {
        return [
            'type' => $this->getType(),
            'description' => $this->description,
            'alias' => $this->alias,
            'label' => $this->label,
            'value' => $this->value,
        ];
    }

    public function withDescription(string $value): self
    {
        $this->description = $value;
        return $this;
    }

    public function withValue($value): self
    {
        $this->value = (string)$value;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->toPayload();
    }

    /**
     * @param string $alias
     * @param string|null $label
     */
    abstract public static function create(string $alias, string $label = "");
}
