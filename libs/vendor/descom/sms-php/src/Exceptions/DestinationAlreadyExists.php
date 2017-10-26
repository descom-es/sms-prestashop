<?php

namespace Descom\Sms\Exceptions;

use InvalidArgumentException;

class DestinationAlreadyExists extends InvalidArgumentException
{
    public static function create($destination)
    {
        return new static("A `{$destination}` destination already exists in a same message.");
    }
}
