<?php

namespace Netflex\Actions\Modules\Form;

final class Alert extends BookingFormField
{
    public string $level;
    public string $message;

    public function __construct()
    {
        parent::__construct("", "");
        $this->level = "info";
        $this->message = "";
    }


    function withLevel(string $level): self
    {
        $this->level = $level;
        return $this;
    }

    function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    function getType(): string
    {
        return "alert";
    }

    function toPayload(): array
    {
        return array_merge(parent::toPayload(), [
            'level' => $this->level,
            'message' => $this->message,
        ]);
    }

    public static function create(string $alias, string $label = "")
    {
        return new static($alias, $label);
    }
}
