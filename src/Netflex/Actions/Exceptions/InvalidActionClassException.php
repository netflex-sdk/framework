<?php

namespace Netflex\Actions\Exceptions;

class InvalidActionClassException extends NetflexappConnectionException
{
    public function __construct($class, $expected)
    {
        $message = "The actionController [$class] does not implement the [$expected] action controller interface";
        parent::__construct($message, 500, null);
    }
}
