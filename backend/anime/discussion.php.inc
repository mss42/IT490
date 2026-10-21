<?php

class discussionDB
{
private $discussiondb;

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

//make discussion -> title of post NOT anime
public function createDiscussion($userid, $animeid, $title, $content)
{
        $tl = $this->$discussiondb->real_escape_string($title);
        $cn = $this->$discussiondb->real_escape_string($content);

	$stmt = $this->discussiondb->prepare("INSERT INTO discussions (userid, anime_id, title, content)
								VALUES (?,?,?,?)");
        $stmt->bind_param("iiss", $userid, $animeid, $tl, $cn);
	$result = $stmt->execute();
}


