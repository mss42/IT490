<?php

session_start()

if (!isset($_SESSION['username'})) {
	header('Location: login.php');
	exit()
}

$articles = [];
$error = '';

try {

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';


function sendToQueue($server, $request) {
	$oldDir = getcwd();
	chdir(__DIR__ . '/../../RabbitMQ');
	$client = new rabbitMQClient("testRabbitMQ.ini", $server);
	chdir($oldDir);
	return $client->send_request($request);
}

$articles = [];
$message = '';

try {
	$request = array(
		"type" => "getNews"
	);

	$response = sendToQueue("dmzServer", $request);

	if (isset($response['returnCode']) && $response['returnCode'] == 0) {
		$articles = $response['articles'] ?? []; 
	} else {
		$message = "Could not retreive anime news";
	}
} catch (Throwable $e) {
	error_log($e->getMessage());
	$message = 'Error connecting to news service';
}
?>
