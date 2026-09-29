<?php

class loginDB
{
private $logindb;

public function __construct()
{
        $this->logindb = new mysqli("127.0.0.1","authUser","12345","authenticationdb");

        if ($this->logindb->connect_errno != 0)
        {
                echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
                exit(1);
        }
        echo "correctly connected to database".PHP_EOL;
}

public function validateLogin($username,$password)
{
        $un = $this->logindb->real_escape_string($username);
        $pw = $this->logindb->real_escape_string($password);
        $statement = "select * from users where username = '$un'";
        $response = $this->logindb->query($statement);

        while ($row = $response->fetch_assoc())
        {
                echo "checking password for $username".PHP_EOL;
                if ($row["password"] == $pw)
                {
                        echo "passwords match for $username".PHP_EOL;
			return $row["userid"];// password match return userid
                }
                echo "passwords did not match for $username".PHP_EOL;
        }
        return false;//no users matched username
}

public function createAccount($username, $password, $email)
{
	$un = $this->logindb->real_escape_string($username);
        $pw = $this->logindb->real_escape_string($password);
	$em = $this->logindb->real_escape_string($email);
	
	$statement = $this->logindb->prepare("INSERT INTO users (username, password, email)
								 VALUES (?, ?, ?)");
	if(!$statement){
                echo "Prep failed:( -->" . $this->sessiondb->error . PHP_EOL;
                return false;
	$statement->bind_param("sss", $un, $pw, $em);
	$result = $statement->execute();

	if($result){
		echo "created account" . PHP_EOL;
		return true;
	} else {
		echo "error creating account" . PHP_EOL;
		return false;
	}

}
}
?>

