<?php 

$url = "https://api.tenrai.org/v1/anime/269/episodes/1";

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
	die("Tenrai returned HTTPS status: " . $httpCode . PHP_EOL);
}

$data = json_decode($response,true);

if(!isset($data["data"])){
	die("Invalid response from Tenrai" . PHP_EOL);
}

echo "Tenrai Connection Successful!" . PHP_EOL;
echo "Anime_ID: " . $data["data"]["mal_id"] . PHP_EOL;
echo "Title: " . $data["data"]["title"] . PHP_EOL;
echo "Score: " . $data["data"]["score"] . PHP_EOL;

