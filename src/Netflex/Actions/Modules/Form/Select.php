<?php

namespace Netflex\Actions\Modules\Form;

final class Select extends BookingFormField
{
    public array $options;

    public function __construct(string $alias, ?string $label)
    {
        parent::__construct($alias, $label);
        $this->options = [];
    }

    function getType(): string
    {
        return "select";
    }

    function toPayload(): array
    {
        return array_merge(parent::toPayload(), ['options' => $this->options]);
    }

    function withOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public static function create(string $alias, ?string $label = "")
    {
        return new static($alias, $label);
    }
}
