<?php

declare(strict_types=1);

require './vendor/autoload.php';
require './SqliteJsonWrapper.php';

$context = \stream_context_create([
    'database' => [
        'file' => './resources/movies.sqlite3',
    ],
]);

$stream = \fopen('sqlj://movies/year/2009', 'rb', false, $context);

$buffer = '';
while (\feof($stream) === false) {
    $buffer .= \fread($stream, 128);
}

echo $buffer;
