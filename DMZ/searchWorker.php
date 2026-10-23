#!/usr/bin/php
<?php

require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';

// call the API and return its "data" part (null if anything went wrong)
function callApi($url)
{
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);   // give up after 10 seconds
	$response = curl_exec($ch);
	curl_close($ch);

	$decoded = json_decode($response, true);
	return $decoded['data'] ?? null;
}

// rename one anime's fields to the names the website and database use
function cleanAnime($a)
{
	return array(
		"animeid"  => $a['mal_id'] ?? null,
		"url"      => $a['url'] ?? '',
		"title"    => $a['title_english'] ?? ($a['title'] ?? 'Untitled'),
		"synopsis" => $a['synopsis'] ?? '',
		"image"    => $a['images']['jpg']['image_url'] ?? '',
		"score"    => $a['score'] ?? null,
		"type"     => $a['type'] ?? '',
		"episodes" => $a['episodes'] ?? null,
		"year"     => $a['year'] ?? null
	);
}

// get a list of anime from a URL (used by both "search" and "top")
function makeList($url)
{
	$list = callApi($url);
	if ($list === null) {
		return array("returnCode" => 1, "message" => "Anime API unavailable, try again");
	}

	$results = array();
	foreach ($list as $a) {
		$item = cleanAnime($a);
		$item['synopsis'] = mb_substr($item['synopsis'], 0, 200);   // short version for the cards
		$results[] = $item;
	}
	return array("returnCode" => 0, "results" => $results);
}

// runs once for every message that arrives; whatever it returns is the reply
function requestManager($request)
{
	$type = $request['type'] ?? '';
	echo "Request: $type" . PHP_EOL;   // shows in the terminal so you can see it working

	// ----- search -----
	if ($type === 'search') {
		$query = trim($request['query'] ?? '');
		if ($query === '' || strlen($query) > 100) {
			return array("returnCode" => 1, "message" => "Enter a search term (max 100 characters)");
		}
		return makeList("https://api.tenrai.org/v1/anime?q=" . urlencode($query) . "&limit=12");
	}

	// ----- top -----
	if ($type === 'top') {
		return makeList("https://api.tenrai.org/v1/top/anime?limit=12");
	}

	// ----- details -----
	if ($type === 'details') {
		$id = $request['animeid'] ?? '';
		if (!ctype_digit((string)$id)) {
			return array("returnCode" => 1, "message" => "Invalid anime id");
		}

		$one = callApi("https://api.tenrai.org/v1/anime/" . $id);
		if ($one === null) {
			return array("returnCode" => 1, "message" => "Anime not found or API unavailable");
		}
		return array("returnCode" => 0, "anime" => cleanAnime($one));
	}

	return array("returnCode" => 1, "message" => "Unknown request type");
}

// connect to the searchServer queue (from testRabbitMQ.ini) and wait for messages
chdir(__DIR__ . '/../RabbitMQ');
$server = new rabbitMQServer("testRabbitMQ.ini", "searchServer");
echo "Waiting for requests on searchServer..." . PHP_EOL;
$server->process_requests('requestManager');
?>

