#!/usr/bin/php
<?php
require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';
require_once __DIR__ . '/anime/anime.php.inc';

function requestManager($request){

        print_r($request);

if (!isset($request['type'])){
        return array(
                "returnCode" => '1',
                "message" => "Missing request type womp womp"
        );
}
//checks if anime if cached;if yes return anime, if no request anime from api
if ($request['type'] == "getAnime"){
	try{
		$animeid = $request['animeid'];

		$iscached = new animeDB();
		return $iscached->cachedAnime($animeid); //cached=false returnCode=1
	}
	catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, plese try again."
                        );
        }
}

//get anime, add to db, return anime
if ($request['type'] == "addAnime"){
        try{
                $animeid = $request['animeid'];
		$title = $request['title'];
		$synopsis = $request['synopsis'];
		$image = $request['image'];
		$score = $request['score'];

                $addAnime = new animeDB();
                return $addAnime->addAnime($animeid, $title, $synopsis, $image, $score);
		//returnCode 0 = added and returned
        }
        catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, plese try again."
                        );
        }
}


}

chdir(__DIR__ . '/../RabbitMQ');

$server = new rabbitMQServer("testRabbitMQ.ini", "animeServer");

echo "Waiting for anime request..." . PHP_EOL;

$server->process_requests('requestManager');

?>
