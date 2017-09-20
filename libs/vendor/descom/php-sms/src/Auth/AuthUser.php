<?php

namespace Descom\Sms\Auth;

class AuthUser implements AuthInterface
{
    /**
     * Define the username for auth.
     *
     * @var string
     */
    private $username;

    /**
     * Define the password for auth.
     *
     * @var string
     */
    private $password;

    /**
     * Create a new authuser instance.
     *
     * @param string $username
     * @param string $password
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get headers for Auth.
     *
     * @return array
     */
    public function headers()
    {
        return [
            'DSMS-User' => $this->username,
            'DSMS-Pass' => $this->password,
        ];
    }
}
