<?php

namespace Descom\Sms\Http;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

class GuzzleV6
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
        $client = new \GuzzleHttp\Client();

        $response = new Response();

        $httpData = [
            'headers'   => $headers,
            'debug'     => $this->debug,
        ];

        if (count($data) > 0) {
            $httpData['json'] = $data;
        }

        try {
            $result = $client->request($verb, 'https://api.descomsms.com/api/'.$path, $httpData);
            $response->status = $result->getStatusCode();
            $response->message = $result->getBody()->getContents();
        } catch (ClientException $e) {
            $response->status = $e->getResponse()->getStatusCode();
            $response->message = $e->getResponse()->getBody(true);
        } catch (RequestException $e) {
            $response->status = 500;
            $response->message = $e->getMessage();
        }

        return $response;
    }
}
