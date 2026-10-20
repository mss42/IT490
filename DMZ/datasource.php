<?php 

function fetchAnime(int $animeid): array
{
	//check for valid anime id
	if ($animeid<=0){
			throw new InvalidArgumentException("Invalid anime ID");
	}

	$url = "https://api.tenrai.org/v1/anime/$animeid/full";
	
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT,20);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

	$response = curl_exec($ch);

	$httpCode = curl_getInfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);

	curl_close($ch);

	if ($response === false) {
		throw new RuntimeException("Tenrai request failed: " . $error);
	}

	if ($httpCode !== 200){
		throw new RuntimeException("Tenrai returned HTTP Status: " . $httpCode);
	}

	$json = json_decode($response, true);

	if(!is_array($json) || !isset($json["data"]["mal_id"])){
		throw new RuntimeException("Invalid response from Tenrai");
	}

	$data = $json["data"];

	return[
		"anime_id" => $data["mal_id"],
		"anime_url" => $data["url"] ?? null,
		"title" => $data["title"] ?? null,
		"synopsis" => $data["synopsis"] ?? null,
		"main_pic" => $data["images"]["jpg"]["large_image_url"] ?? null,
		"score" => $data["score"] ?? null
	];

}
