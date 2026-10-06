#!/usr/bin/php
<?php
require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';

function requestManager($request){

	print_r($request);

if (!isset($request['type'])){
       	return array(
		"returnCode" => '1',
		"message" => "Missing request type womp womp"
	);
}
 if ($request['type'] == "login"){
	 $username = trim($_POST['username'] ?? '');
	 $password = $_POST['password'] ?? '';
	 
	 if (username === '' || $password === '')
	 {
		 return array(
			 "returnCode" => 1,
			 "message" => "Username and password are required"
		 );
	 }

	 try
	 {	
		 //this will go into the database and check if username exists
		 $db = new mysqli('100.105.60.109', 'dbUser', '12345', 'authenticationdb' );
		 
		 $chkUser = $db->prepare("SELECT userid, username, password FROM users WHERE username = ?");
		 $chkUser->bind_param('s', $username);
		 $chkUser->execute();
		 $user = $chkUser->get_result()->fetch_assoc();
		 $chkUser->close();
	
	if(!$user || $user['password'] != $password)
	{
		echo " login failed for '$username'" . PHP_EOL;
		return array(
			"returnCode" => 1,
			"message" => "Invalid username or password."
		);
	}

	//this will be the lines of code that generate a session key AND store it
	$sessionId = bin2hex(random_bytes(32));
	$addSession = $db->prepare("INSERT INTO sessions (sessionid, userid, expires_at)
	VALUES (?, ?, NOW() + INTERVAL 1 HOUR)");
	$addSession->bind_param('si', $sessionId, $user['userid']);
	$addSession->execute();
	$addSession->close();
	$db->close();

	echo "login OK for '{$user['username']}', session key stored" . PHP_EOL;
	return array(
		"returnCode" => 0,
		"message" => "Login successful",
		"sessionId" => $sessionId,
		"username" => $user['username']
		);
	}
	 catch (mysqli_sql_exception $exception)
	{
		echo " DB Error: " . $exception->getMessage() . PHP_EOL;
		return array(
			"retunrCode" => 1,
			"message" => "Database error, please try again"
		);
	}
	catch (Throwable $exception)
	{
		echo " Error: " . $exception->getMessage() . PHP_EOL;
		return array(
			"returnCode" => 1,
			"message" => "Server error, plese try again."
			);
		}
	}
}
chdir(__DIR__ . '/../RabbitMQ');

$server = new rabbitMQServer("testRabbitMQ.ini", "loginServer");

echo "Waiting for login information on loginServer..." . PHP_EOL;

$server->process_requests('requestManager');
?>
