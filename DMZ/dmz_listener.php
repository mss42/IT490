#!/usr/bin/php
<?php

require_once __DIR__ . '/rabbitMQLib.inc';
require_once __DIR__ . '/dmz_worker.php';

function sendToDB($addAnimeRequest)
{
	$client = new rabbitMQClient("testRabbitMQ.ini", "animeServer");
	return $client ->send_request($addAnimeRequest);
}

function dmzRequestHandler($request)
{
	try{
		if(!is_array($request)){
			throw new InvalidArgumentException("Reques was not valid JSON");
		}
		echo "Request: " . json_encode($request) . PHP_EOL;

		if(($request['type'] ?? '') === 'getNews'){
			return fetchNews();
		}

		if(($request['type'] ?? '') === 'getRecommendations'){
			return fetchRecommendations($request['liked'] ?? [], $request['exclue'] ?? []);
		}

		//validate request and call Tenrai
		
		$addAnime = processDMZRequest($request);
		echo "Fetched anime {$addAnime['animeid']}: {$addAnime['title']}" . PHP_EOL;

		//hand to db
		$dbResponse = sendToDB($addAnime);
		echo "DB response: " . json_encode($dbResponse) . PHP_EOL;

		if (!is_array($dbResponse)){
			throw new RuntimeException("No valid response from DB");
		}


		return $dbResponse;
	} catch(Throwable $e){
		echo "DMZ error: " . $e->getMessage() . PHP_EOL;
		return array(
			"returnCode" => 1,
			"cached" => false,
			"anime" => null,
			"message" => $e->getMessage()
		);
	}
}

chdir(__DIR__);

$server = new rabbitMQServer("testRabbitMQ.ini", "dmzServer");

echo "DMZ waiting for fetchAnime requests..." . PHP_EOL;

$server->process_requests('dmzRequestHandler');


