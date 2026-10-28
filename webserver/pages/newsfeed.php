<?php

session_start();

if (!isset($_SESSION['username'])) {
	header('Location: login.php');
	exit();
}

$articles = [];
$errors = '';



require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';


function sendToQueue($server, $request) {
	$oldDir = getcwd();
	chdir(__DIR__ . '/../../RabbitMQ');
	$client = new rabbitMQClient("testRabbitMQ.ini", $server);
	chdir($oldDir);
	return $client->send_request($request);
}

$articles = [];
$message = '';

try {
	$request = array(
		"type" => "getNews"
	);

	$response = sendToQueue("dmzServer", $request);

	if (isset($response['returnCode']) && $response['returnCode'] == 0) {
		$articles = $response['articles'] ?? []; 
	} else {
		$message = "Could not retreive anime news";
	}
} catch (Throwable $error) {
	error_log($error->getMessage());
	$message = 'Error connecting to news service';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Anime News Feed</title>
	<link rel="stylesheet" href="main.css">
</head>

<body>
	<nav>
	<a href="landing.php">Home</a>
	<a href="recommendation.php">Recommendations</a>
	</nav>

	<h1>Latest Anime News!</h1>
	
	<div class="news-container">
	
	<?php foreach ($articles as $article) { ?>

		<div class="news-card">
		
		<h2> <?php echo htmlspecialchars($article["title"] ?? ""); ?> </h2>

		<p> <strong>Published:</strong> <?php echo htmlspecialchars($article["date"] ?? ""); ?> </p>
		
		<p> <?php echo htmlspecialchars($article["summary"] ?? ""); ?> </p>
		
		<!-- put in a filter for the URL remove it if it just stops working -->
		<?php
		$link = $article["link"] ?? "";
		$scheme = parse_url($link, PHP_URL_SCHEME);
		?>
		
		<?php if (filter_var($link, FILTER_VALIDATE_URL) &&
		in_array($scheme, ["http", "https"], true)) { ?>

		<a href="<?php echo htmlspecialchars($link, ENT_QUOTES, "UTF-8"); ?>" 
		target="_blank" rel="noopener norefferer"> Read Full Article </a>
	<?php } ?>
</div>

<?php } ?>

</div>

</body>
</html>
