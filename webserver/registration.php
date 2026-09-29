#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');



$client = new rabbitMQClient("testRabbitMQ.ini","authServer");

//building array to sent to worker with data sent from register.php

$request = array();
$request['type'] = "register";
$request['email'] = $_POST['email'];
$request['first_name'] = $_POST['first_name'];
$request['last_name'] = $_POST['last_name'];
$request['username'] = $_POST['username'];
$request['password'] = $_POST['password'];
 

echo "Sending a request" . PHP_EOL;

print_r($request);


$response = $client->send_request($request);

//$response = $client->publish($request));

echo "client received response: " . PHP_EOL;
print_r($response);

//Testing if registration work direct user to login if not stay on register page

if (isset($response['returnCode']) && $response['returnCode'] == 0) {
	//registeration worked
	//user goes to login page
	header("Location: index.php");
	exit();
}

else {
	//registration failed
	//give an error message
	echo "Registration failed." . PHP_EOL;

	//user stay on registration page
	header("Location: register.php");
	exit();
}

//end of test code

echo "\n";

echo "\n";

echo "Client doneee" . PHP_EOL;

?>
</php>
