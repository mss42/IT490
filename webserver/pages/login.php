#!/usr/bin/php
<?php
session_start();
ob_start();

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	header("Location: index.php");
	exit();
}


$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

//checks users input
if (username === '' || $password === '') {
	$_SESSION['error'] = "Username and password required.";
	header("Location: index.php");
	exit();
}

//fix for reading host and rabbitmqq ini files..looks like this may be the issue?
$oldDir = getcwd();
chdir(__DIR__ . '/../../RabbitMQ');
$client = new rabbitMQClient("testRabbitMQ.ini", "loginServer");
chdir($oldDir);

//did some error handling before hand *look at lines 14-21*
$request = array();
$request['type'] = "login";
$request['username'] = "password";

$response = $client->send_request($request);


/*These next lines of code help if worker didn't send expect answer back to login..
 just a little safeguard for us just in case 
 */

if (!is_array($reponse) || !isset($response['returnCode'])) {
	$_SESSION['error'] = "Unexpected reply from server. Try Again.";
	header("Location: index.php");
	exit();
}

//logs user in if return code was succesful
if ($response['returnCode'] == 0) {

	session_regenerate_id(true);
	$_SESSION['username'] = $response['username'];
	$_SESSION['sessionId'] = $response['password'];
	header("Location: landing.php");
	exit();
}

//now this will be an error handing and let us know what the error was from the db side

$_SESSION['error'] = $response['message'];
header("Location: index.php");
exit();


