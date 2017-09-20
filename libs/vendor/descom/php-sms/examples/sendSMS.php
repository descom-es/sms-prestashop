<?php

use Descom\Sms\Auth\AuthUser;
use Descom\Sms\Message;
use Descom\Sms\Sms;

require '../vendor/autoload.php';

if ($argc < 5) {
    echo 'Usage '.$argv[0]." username password destination text.\n";
    exit(1);
}

$sms = new Sms(new AuthUser($argv[1], $argv[2]));

$message = new Message();

$message->addTo($argv[3])->setText($argv[4]);

$result = $sms->addMessage($message)
        ->setDryrun(true)
        ->send();

var_dump($result);
