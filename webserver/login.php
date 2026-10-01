#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



$client = new rabbitMQClient("testRabbitMQ.ini","authServer");

//building array to sent to worker with data sent from register.php

$request = array();
$request['type'] = "login";
$request['username'] = $_POST['username'];
$request['password'] = $_POST['password'];
 

echo "Sending login request" . PHP_EOL;

print_r($request);


$response = $client->send_request($request);

//Kev add the front end connection side, if needed call so i can help :D

//$response = $client->publish($request));

echo "client received response: " . PHP_EOL;
print_r($response);

if (isset($response['returnCode']) && $response['returnCode'] == 0) {
	header("Location: landing.php");
	exit();
}
else {
	echo "Login Failed" . PHP_EOL;

	//user stays on login
	header("Location: index.php");
	exit();
}

