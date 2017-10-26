<?php

namespace Descom\Sms\Auth;

interface AuthInterface
{
    /**
     * Get headers for Auth.
     *
     * @return array
     */
    public function headers();
}
