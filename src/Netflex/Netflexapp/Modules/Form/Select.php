<?php

namespace Netflex\Netflexapp\Modules\Form;


class Select extends BookingFormField
{
    public array $options;

    public function __construct(string $alias, ?string $label, array $options)
    {
        parent::__construct($alias, $label);
        $this->options = $options;
    }

    function getType(): string
    {
        return "select";
    }

    function toPayload(): array
    {
        return array_merge(parent::toPayload(), ['options' => $this->options]);
    }

}
