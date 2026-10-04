<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

?>



<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" contact="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="main.css">
	<title>Register</title>
</head>
<body>
	<div class="Authentication_container">
		<h1>Register Here</h1>
		<form action="registration.php" method="POST">
			<div class="Authentication_form">
				<label for="email">Email</label>
				<input type="email" name="email" required />
			</div>
			<div class="Authentication_form">
				<label for="first_name">First Name</label>
				<input type="text" id="first_name" name="first_name" required />
			</div>
			<div class="Authentication_form">
				<label for="last_name">Last Name</label>
				<input type="text" id="last_name" name="last_name" required />
			</div>
			<div class="Authentication_form">
				<label for="username">Username</label>
				<input type="text" id="username" name="username" required />
			</div>
			<div class="Authentication_form">
				<label for="password">Password</label>
				<input type="text" id="password" name="password" required />
			</div>
			<input type="submit" value="Register" class="submit_button"/>
		</form>
	</div>
<?php
//This will run after user submits the Authentication form
	if($_SERVER["REQUEST_METHOD"] == "POST") {
		$errors = [];

		//retrieves data from the Authentication form 
			$email = $_POST["email"];
			$first_name = $_POST["first_name"];
			$last_name = $_POST["last_name"];
			$username = $_POST["username"];
			$password = $_POST["password"];

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors[] = "You entered a invaild email format";
			}


			if (empty($first_name) || empty($last_name)) {
				$errors[] = "You are required to enter your first and last name";
			}


			if (empty($username)) {
				$errors[] = "A username is required";
			}


			if (empty($password)) {
				$errors[] = "A password is required";
			}
	}
?>	
</body>
</html>
