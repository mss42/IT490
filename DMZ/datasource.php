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
	
	$genres = [];

	foreach($data["genres"] ?? [] as $g){
		$genres[] = $g["name"];
	}

	return[
		"anime_id" => $data["mal_id"],
		"anime_url" => $data["url"] ?? null,
		"title" => $data["title_english"] ?? ($data["title"] ??  null),
		"synopsis" => $data["synopsis"] ?? null,
		"main_pic" => $data["images"]["jpg"]["large_image_url"] ?? null,
		"score" => $data["score"] ?? null, 
		"genres" => implode(",",$genres)
	];

}

function fetchNews(): array 
{
	$url = "https://www.animenewsnetwork.com/all/rss.xml?ann-edition=us";
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "IT490-AnimeApp/1.0");

	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$error = curl_error($ch);
	curl_close($ch);

	if ($response === false) {
		throw new RuntimeException("News request failed: " . $error);
	}
	if ($httpCode !== 200){
		throw new RuntimeException("News feed returned HTTP Status: " . $httpCode);
	}

	libxml_use_internal_errors(true);
	$xml = simplexml_load_string($response);
	if ($xml == false || !isset($xml->channel->item)){
		throw new RuntimeExceptions("Invalid news feed");
	}

	$articles = [];
	foreach($xml->channel->item as $item){
		$summary = trim(strip_tags((string) $item->description));
		$articles[] = [
			"title" => (string) $item->title,
			"link" => (string) $item->link,
			"date" => (string) $item->pubDate,
			"summary" => mb_substr($summary, 0, 200)
		];
		if(count($articles) >= 10) {
			break;
		}
	}

	return[
		"returnCode" => 0,
		"articles" => $articles
	];
}


function fetchAnimeRecs(int $animeid): array
{
	$url = "https://api.tenrai.org/v1/anime/" .$animeid . "/recommendations";

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);

	$response = curl_exec($ch);
	$httpCode = curl_getInfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	if($response === false || $httpCode !== 200){
		return[];
	}

	$json = json_decode($response, true);
	return $json["data"] ?? [];
}

function fetchRecommendations(array $liked, array $exclude = []): array
{
	$liked = array_slice($liked, 0, 5);
	if(count($liked) === 0){
		return ["returnCode" => 1, "message" =>"Rate some anime 4 or 5 to get recommendations"];
	}

	$skip = [];
	foreach($exclude as $id){
		$skip[(int)$id] = true;
	}
	foreach ($liked as $l) {
		$skip[(int)($l["animeid"] ?? 0)] = true;
	}
	$picks = [];
	foreach($liked as $l){
		$likedId = (int)($l["animeid"] ?? 0);
		if($likedId <= 0) {
			continue;
		}
		$likedTitle = $l["title"] ?? "an anime you rated highly";

		foreach(fetchAnimeRecs($likedId) as $rec){
			$id = (int)($rec["entry"]["mal_id"] ?? 0);
			if($id <= 0 || isset($skip[$id])) {
				continue;
			}
			if(!isset($picks[$id])){
				$picks[$id] = [
					"animeid" => $id,
					"title" => $rec["entry"]["title"] ?? "Untitled",
					"image" => $rec["entry"]["images"]["jpg"]["image_url"] ?? "",
					"votes" => 0,
					"matches" => 0,
					"reason" => "Because you liked " . $likedTitle
				];
			}
			$picks[$id]["votes"] += (int)($rec["votes"] ?? 0);
			$picks[$id]["matches"] += 1;
		}
	}

	usort($picks, function ($a, $b){
		if($a["matches"] !== $b["matches"]){
			return $b["matches"] - $a["matches"];
		}
		return $b["votes"] - $a["votes"];
	});

	return [
		"returnCode" => 0,
		"results" => array_slice($picks, 0, 10)
	];
}

