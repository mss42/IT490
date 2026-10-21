<?php

class animeDB
{
private $animedb;

public function __construct()
{
        $this->animedb = new mysqli("127.0.0.1","animeUser","12345","animedb");

        if ($this->animedb->connect_errno != 0)
        {
                echo "Error connecting to database: ".$this->animedb->connect_error.PHP_EOL;
                exit(1);
        }
        echo "correctly connected to database".PHP_EOL;
}

public function cachedAnime($animeid){

	$id = $this->animedb->$animeid;

        $iscached = $this->animedb->prepare("SELECT * FROM anime WHERE anime_id = ?");
	$iscached->bind_param("i", $animeid);
	$iscached->execute();

	$result = $iscached->get_result();

	if($row = $result->fetch_assoc()){
		iscached->close();
		//get anime from api
		return array( 
		        "returnCode" => 0,
			"cached" => true,
			"anime" => $row
		);
	}
		//get anime from api
		$iscached->close();
		return array(
			"returnCode" => 1,
			"cached" => false,
			"anime" => null,
			"message" => "Anime not cached, request from api"
		);
}

public function addAnime($animeid, $title, $synopsis, $image, $score){

	$addstmt = $this->animedb->prepare("INSERT INTO anime (anime_id,title,synopsis,main_pic,score)
								VALUES (?,?,?,?,?)");
	$addstmt->bind_param("isssd", $animeid, $title, $synopsis, $image, $score);
	$addstmt->execute();
	$addstmt->close();

	//return anime
	$getAnime = $this->animedb->prepare("SELECT * FROM anime WHERE anime_id = ?");
        $getAnime->bind_param("i", $animeid);
        $getAnime->execute();

        $result = $getAnime->get_result();

        if($row = $result->fetch_assoc()){
                $getAnime->close();
                return array(
                        "returnCode" => 0,
                        "cached" => true,
                        "anime" => $row
                );
        }
	$getAnime->close();
	return array(
		"returnCode" => 2,
		"cached" => false,
		"anime" => null,
		"message" => "Anime inserted, not retrievable"
	);
}


}
?>
