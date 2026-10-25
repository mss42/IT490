<?php

session_start();
ob_start();


require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

//checks to see if page is submited
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if (isset($_POST['username'])) {
		$username = trim($_POST['username']);
	}
	else {
		$username = '';
	}

	if (isset($_POST['password'])) {
		$password = trim($_POST['password']);
	}
	else {
		$password = '';
	}
}

/*
	if ($username === '' || $password === '') {
		$_SESSION['error'] = "A username and password is required";
		header("Location: login.php");
		exit();
	}
 */
	//save dir
	$oldDir = getcwd();

	//change to rabbit folder
	chdir(__DIR__ . '/../../RabbitMQ');

	$client = new rabbitMQClient("testRabbitMQ.ini", "loginServer");

	//change back to the original DIR
	chdir($oldDir);

	$request = array();
	$request['type'] = "login";
	$request['username'] = $username;
	$request['password'] = $password;

	
	
	$response = $client->send_request($request);
/*
	if (!is array($response) || !isset($response['returnCode'])) {
		$_SESSION['error'] = "Unexpected error from server. Try Again";
		header("Location: login.php");
		exit();
	}
*/ 
	//login successful
	if ($response['returnCode'] == 0) {

		//create a session id 
		session_regenerate_id(true);

		//checks to see if a username was returned
		if (isset($response['username'])) {
			$_SESSION['username'] = $response['username'];
		}
		else {
			$_SESSION['username'] = $username;
		}
		
		//checks for a session id
		if (isset($response['sessionID'])) {
			$_SESSION['sessionID'] = $response['sessionID'];
		}
		else {
			$_SESSION['sessionID'] = '';
		}

		header("Location: landing.php");
		exit();
	}


?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial scale=1.0">
	<link rel="stylesheet" href="main.css">
	<title>Login</title>
</head>
<body>
	<div class="Authentication_container">
		<h1>Login</h1>
		<form action="login.php" method="POST">
			<div class="Authentication_form">
				<label for="username">Username</label>
				<input type="text" id="username" name="username" required />
			</div>
			<div class="Authentication_form">
				<label for="password">Password</label>
				<input type="text" id="password" name="password" required />
			</div>
			<input type="submit" id="login" value="Login" class="submit_button" />
		</form>
		
		<div class="register_link">
			<a href="register.php" class="button">Create Account</a>
		</div>
	</div>
</body>
</html>
