#!/usr/bin/php
<?php
require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';



$client = new rabbitMQClient("testRabbitMQ.ini","testAnimeServer");

$request = array();
$request['type'] = "Connectivity_test";
$request['source'] = gethostname();
$request['message'] = "IM COMMUNICATING VIA RABBITTTT I CONQUERED ITTT";
echo "Sending a request" . PHP_EOL;

print_r($request);


$response = $client->send_request($request);


//$response = $client->publish($request);

echo "client received response: " . PHP_EOL;
print_r($response);

echo "\n";

echo "\n";

echo "Client doneee" . PHP_EOL;

?>
