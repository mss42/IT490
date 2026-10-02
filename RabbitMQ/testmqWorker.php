#!/usr/bin/php
<?php
require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';

//function manages requests sent by MQ if statments print messages depending if the connectivity test was valid

function requestManager($request)
{
	print_r($request);
	
	if (!isset($request['type']))
	{
		return array(
			"returnCode" => 1,
			"message" => "Missing request type womp womp"
		);
	}
	
	if ($request['type'] == "Connectivity_test")
	{
		return array(
			"returnCode" => 0,
			"message" => "Backend HAS OFFICIALLY received the messaage",
			"received_by" => gethostname()
		);
	}

	return array(
		"returnCode" => 1,
		"message" => "unknown request type it didnt work ;("
	);
}

$server = new rabbitMQServer("testRabbitMQ.ini","testAnimeServer");

echo "Waiting for RabbitMQ message... please come.. your my only hope.." . PHP_EOL;

$server->process_requests('requestManager');

?>
