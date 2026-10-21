<?php

require_once __DIR__ . '/datasource.php';

function processDMZRequest($request)
{
	if(!isset($request['type'])){
		throw new Exception("Missing request type");
	}
	if($request['type'] !== 'fetchAnime') {
		throw new Exception("Invalid request type");
	}
	if(!isset($request['animeid'])){
		throw new Exception("Missing anime ID");
	}

	$animeid = filter_var(
		$request['animeid'],
		FILTER_VALIDATE_INT,
		["options" => ['min_range' => 1]]
	);

	if($animeid === false){
		throw new Exception("Invalid anime ID");
	}


	$anime = fetchAnime($animeid);


	return[
		"type" => "addAnime",		
		"animeid"=> $anime["anime_id"],
		"title" => $anime["title"],
		"synopsis" => $anime["synopsis"],
		"image" => $anime["main_pic"],
		"score" => $anime["score"]
	];

}
