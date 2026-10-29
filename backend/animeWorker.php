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
		$genre = $request['genres'];

                $addAnime = new animeDB();
                return $addAnime->addAnime($animeid, $title, $synopsis, $image, $score, $genre);
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

if( $request['type'] == 'getLikedAnime'){
	$username = $request['username'];
	try{
		$db = new mysqli('127.0.0.1', 'animeUser', '12345', 'animedb' );

		$getLikes=$db->prepare("SELECT a.anime_id AS animeid, a.title
					FROM reviews r
					JOIN authenticationdb.users u ON u.userid = r.userid
					JOIN anime a ON a.anime_id = r.anime_id
					WHERE u.username = ? AND r.rating >= 4
					ORDER BY r.rating DESC;");
		$getLikes->bind_param('s', $username);
		$getLikes->execute();
		$result = $getLikes->get_result();
		$likes = array();

		while($row = $result->fetch_assoc()){
			$likes[] = $row;
		}
		$getLikes->close();
		$getAll=$db->prepare("SELECT r.anime_id FROM reviews r
					JOIN authenticationdb.users u  ON u.userid = r.userid
					WHERE u.username = ?;");
		$getAll->bind_param('s', $username);
		$getAll->execute();
		$result = $getAll->get_result();
		$all = array();
		while ($row = $result->fetch_assoc()){
			$all[] = (int)$row['anime_id'];
		}
		$getAll->close();
		if (empty($likes)){
			return array("returnCode" => 1, "message" => "User didn't like anything",
					"liked" => array(),
					"rated" => $all);
		}
		return array("returnCode" => 0, "liked" => $likes, "rated" => $all);
		
	}catch(mysqli_sql_exception $exception) {
	 	echo "DB Error: " . $exception->getMessage() . PHP_EOL;
		return array("returnCode"=>2, "message" =>"Database Error");
	}catch(Throwable $exception) {
		echo "Error: " . $exception->getMessage() . PHP_EOL;
		return array("returnCode" =>2, "message"=> "Server Error");
	}

} 
}

chdir(__DIR__ . '/../RabbitMQ');

$server = new rabbitMQServer("testRabbitMQ.ini", "animeServer");

echo "Waiting for anime request..." . PHP_EOL;

$server->process_requests('requestManager');

?>
