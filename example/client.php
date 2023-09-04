<?php

include __DIR__ . '/../vendor/autoload.php';


$client = new Roiwk\SSEClient\Client('http://127.0.0.1:8888');
$client->addEventListener('ping', function ($data) use($client) {
    echo "Received ping event: $data\n";
    $client->close();
});
$client->start();
