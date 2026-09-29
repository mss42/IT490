#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//php request manager to submit new registration info.. also checks if user already exists.. lets hope this works ;(

function requestManager($request)
{
	print_r($request);
	
	if (!isset($request['type']))
	{
		return array(
			"returnCode" => '1',
			"message" => "Missing request type womp womp"
		);
	}

	//accessing database
	
	if ($request['type'] == "register")
	{
		$db = new mysqli('127.0.0.1', 'dbUser', '12345', 'authenticationdb' )

	}

//checking if databse already has exisitng user

	
	if ($db->connect_error) {
		return array(
			"returnCode" => 1,
			"message" => "Database connectivty FAILED try again LMAOO"
		);
	}

	// Prepare SQL Statement, then set parameters, then execute the statement. btw FROM = ?? waiting for table..

	$chkUser = $db->prepare("SELECT username, email, FROM ???, WHERE username = ? OR email = ?");

	$chkUser->bind_param('ss',$request['username'], $request['email']);

	$chkUser->execute();

	$result = $chkUser->get_result();



	//checks to see if there is any lines that got back from chkUser then 

	if ($result->num_rows > 0) {

		//grabs current row and checks if there aren't any dupes
		
		$existingUser = $result->fetch_assoc()
		
			if ($existingUser['username'] == $request['username']){
				return array(
					"returnCode" => 1,
					"message" => "Username Already Exisits"
				);
			}

			if ($existingUser['email'] == $request['email']){
				return array(
					"returnCode" => 1,
					"message" => "Email is already in use"
				);
			}

	}

	// no dupes... time to insert it :D also rememeber table undefined idk what it is yet :(
	
	$insertUser = $db->prepare("INSERT INTO ??? (email, first_name, last_name, username, password)
		VALUES (?, ?, ?, ?, ?");

	$insertUser->bind_param("sssss", $request['email'], $request['first_name'], $request['last_name'],
		$request['username'], $request['password']);

	if ($insertUser->execute()){
		return array(
			"returnCode"=> 1,
			"message" => "you failed NO REGISTRATION COMPLETED :/"
		);
	}

	

	return array(
		"returnCode" => 1,
		"message" => "unknown request type it didnt work ;("
	);
}

$server = new rabbitMQServer("testRabbitMQ.ini","testAnimeServer");

echo "Waiting for registration information..." . PHP_EOL;

$server->process_requests('requestManager');

?>
