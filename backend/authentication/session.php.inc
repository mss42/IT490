<?php
class sessionDB
{
private $sessiondb;

public function __construct()
{
        $this->sessiondb = new mysqli("100.105.60.109","sessionUser","12345","authenticationdb");

        if ($this->sessiondb->connect_errno != 0)
        {
                echo "Error connecting to database: ".$this->sessiondb->connect_error.PHP_EOL;
                exit(1);
        }
        echo "correctly connected to database".PHP_EOL;
}



public function createSession($userid)
{
	$session = bin2hex(random_bytes(32));
	$date = new DateTime();
	$date->modify('+1 hour');
	$expires_at = $date->format('Y-m-d H:i:s');

	$statement = $this->sessiondb->prepare("INSERT INTO sessions (sessionid, userid, expires_at)
			VALUES (?, ?, ?)");
	if(!$statement){
		echo "Prep failed:( -->" . $this->sessiondb->error . PHP_EOL;
		return false;
	}
	$statement->bind_param("sis", $session, $userid, $expires_at);
	$result = $statement->execute();
	
	if($result){
		return true;
	}
	return false;
}

public function validateSession($sessionid)
{
	$currentTime = date('Y-m-d H:i:s');
	$statement = $this->sessiondb->prepare("SELECT * FROM sessions WHERE sessionid = ?");
	$statement->bind_param("s", $sessionid);
	$statement->execute();
	$result = $statement->get_result();

	if($result->num_rows === 0){
		echo "session does not exist" . PHP_EOL;
		return false;
	}

	$row = $result->fetch_assoc();
	$expires_at = $row["expires_at"];

	if($currentTime < $expires_at){
		echo "session valid";
		return true;
	} else {
		echo "session expired";
		return false;
	}
}

}
?>
