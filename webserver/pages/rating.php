<?php
session_start();

//checks to see if user is logged in by looking at username
if (!isset($_SESSION['username'])) {
	header("Location: login.php");
	exit();
}

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

//get anime id from URL
$animeid = $_GET['animeid'] ?? '';



$message = '';
$reviews = array();
$average = 0;
$total = 0;



?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initail-scale=1.0">
	<title>Rating and Review</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>
	<p><a href="landing.php">Back to Landing Page</a></p>
	
	<h1>Rating and Review</h1>

