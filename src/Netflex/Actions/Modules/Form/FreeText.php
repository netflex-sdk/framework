<?php

namespace Netflex\Actions\Modules\Form;

final class FreeText extends BookingFormField
{
    public string $message = "";

    private function __construct($alias, $label)
    {
        parent::__construct("", "");
    }


    function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    function getType(): string
    {
        return "freetext";
    }

    public function toPayload(): array
    {
        return array_merge(parent::toPayload(), [
            'value' => $this->message,
        ]);
    }


    public static function create(string $alias, string $label = ""): FreeText
    {
        return new static($alias, $label);
    }
}
