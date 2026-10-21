#!/usr/bin/php
<?php

require_once __DIR__ . '/rabbitMQLib.inc';

chdir(__DIR__);

$animeid = isset($argv[1]) ? (int)$argv[1] : 269;

$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");

echo "Sending fetchAnime for $animeid..." . PHP_EOL;

$response = $client->send_request(array(
	"type" => "fetchAnime",
	"animeid" => $animeid
));

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
