<?php

use Descom\Sms\Auth\AuthUser;
use Descom\Sms\Sms;

require '../vendor/autoload.php';

if ($argc < 3) {
    echo 'Usage '.$argv[0]." username password.\n";
    exit(1);
}

$sms = new Sms(new AuthUser($argv[1], $argv[2]));

$balance = $sms->getBalance();

echo 'Balance: '.$balance."\n";
