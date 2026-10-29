<?php 
require_once __DIR__ . '/dmz_worker.php';

$request = [
	"type" => "fetchAnime",
	"animeid" => 269
];

try{
	$response = processDMZRequest($request);

	echo json_encode(
		$response,
		JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
	) . PHP_EOL;
} catch(Throwable $e){
	echo "Error: " . $e->getMessage() . PHP_EOL;
}
