<?php 

$url = "https://api.jikan.moe/v4/anime/269/full";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,$url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if($response === false){
	die("Request Failed: " .curl_error($ch) . PHP_EOL);
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if($httpCode !== 200){
	die("Jikan returned HTTPS status: " . $httpCode . PHP_EOL);
}

$data = json_decode($response,true);

if(!isset($data["data"])){
	die("Invalid response from Jikan" . PHP_EOL);
}

echo "Jikan Connection Successful!" . PHP_EOL;
echo "Anime_ID: " . $data["data"]["mal_id"] . PHP_EOL;
echo "Title: " . $data["data"]["title"] . PHP_EOL;
echo "Score: " . $data["data"]["score"] . PHP_EOL;

