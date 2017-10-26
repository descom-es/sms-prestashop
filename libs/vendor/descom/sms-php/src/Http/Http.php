<?php

namespace Descom\Sms\Http;

use GuzzleHttp\ClientInterface;

class Http
{
    private $debug = false;

    /**
     * Send Request to API.
     *
     * @param string $verb
     * @param string $path
     * @param array  $headers
     * @param array  $data
     *
     * @return \Descom\Sms\Http\Response
     */
    public function sendHttp($verb, $path, array $headers, array $data = [])
    {
        $version = (string) ClientInterface::VERSION;

        if ($version[0] === '6') {
            $sms = new GuzzleV6();

            return $sms->sendHttp($verb, $path, $headers, $data);
        } elseif ($version[0] === '5') {
            $sms = new GuzzleV5();

            return $sms->sendHttp($verb, $path, $headers, $data);
        }
    }
}
