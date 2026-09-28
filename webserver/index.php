<!DOCTYPE>
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
		
