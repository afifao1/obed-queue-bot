<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage('Hello, world!');
})->description('The start command!');
