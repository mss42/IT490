<?php

//checks to see if user is logged in by looking at username
if (!isset($_SESSION['username'])) {
	header("Location: index.php");
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Login Successful</title>
</head>
<body>
	<h1>Welcome in we cried over 20 times during this proccess, but now it FUCKING WORKS<h1>
</body>
