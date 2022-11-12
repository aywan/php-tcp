<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('Content-type: text/plain',);

echo "hello", PHP_EOL;

$c = new \app\Client(
    $_SERVER['REMOTE_ADDR'],
    '8081',
    false,
    true
);

$c->open();

$s = "test " . random_int(10, 99) . PHP_EOL;

$c->send($s);
$c->send('STOP' . PHP_EOL);

echo $s, \PHP_EOL;

