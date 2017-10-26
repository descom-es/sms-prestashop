<?php

namespace Descom\Sms\Exceptions;

use Exception;

class RequestFail extends Exception
{
    public static function create($message)
    {
        return new static($message);
    }
}
