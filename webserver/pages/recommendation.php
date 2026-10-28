<?php
session_start();

//once again checks if user is logged in

if (!isset($_SESSION['username'])) {
	header("Location: login.php");
	exit();

}

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

//sends request to the backend an returns the reply
function sendToQueue($server, $request) {
	$oldDir = getcwd();
	chdir( __DIR__ . '/../../RabbitMQ');
	$client = new rabbitMQClient("testRabbitMQ.ini", $server);
	chdir($oldDir);
	return $client->send_request($request);
}

$recommendation = [];
$message = '';

try {
	//1. ask the DB which anime this user rated 4 or 5, and everything they've rated
	$db = sendToQueue("animeServer", array("type" => "getLikedAnime", "username" => $_SESSION['username']));

	//2. ask the DMZ for anime recommended by fans of those anime
	$rec = sendToQueue("dmzServer", array("type" => "getRecommendations", "liked" => $db['liked'] ?? [], "exclude" => $db['rated'] ?? []));

	if (isset($rec['returnCode']) && $rec['returnCode'] == 0) {
		$recommendation = $rec['results'] ?? [];
	}
}
catch (Throwable $error) {
	$message = 'Could not reach server';
}

// this function helps later on with displaying the animes
function displayText($value) {
	return htmlspecialchars(
		(string)$value,
		ENT_QUOTES,
		'UTF-8'
	);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title>My Anime Recommendations</title>
	<link rel="stylesheet" href="main.css">

	<style>
		.recommendations {
			max-width: 1100px;
			margin: 40px auto;
			padding: 20px;
		}

		.recommendation-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(240px,1fr));
			gap: 20px;
			margin-top: 25px;
		}

		.recommendation-card h3 {
			margin-top: 0;
		}

		.recommendation-card img {
			width: 100%;
			border-radius: 8px;
		}

		.recommendation-card a {
			display: inline-block;
			margin-top: 10px;
		}

	</style>

</head>

<body>

	<nav>
	<a href="landing.php">Home</a>
	<a href="recommendation.php">Recommendations</a>
	</nav>

<main class="recommendations">

	<h1> Recommended For You </h1>

	<p> Discover Anime based on YOUR ratings and preferences </p>

	<?php if ($message !== '') { ?>
		<p><?php echo displayText($message); ?></p>
	<?php } ?>

	<?php if (empty($recommendation)) { ?>

		<h2>No Recommendations yet</h2>

		<p> Come back when you have rated some anime so we can get a little taste of what you like </p>

		<a href="landing.php">Explore anime</a>
	<?php } else { ?>
		
	<div class="recommendation-grid">
		
		<?php foreach ($recommendation as $anime) { ?>

			<div class="recommendation-card">

		<?php if (!empty($anime['image'])) { ?>
			<img src="<?php echo displayText($anime['image']); ?>" alt="<?php echo displayText($anime['title']); ?>">
		<?php } ?>

		<h3> <?php echo displayText($anime['title']); ?> </h3>

		<?php if (!empty($anime['genres'])) { ?>
			<p> <strong>Genres</strong> <?php echo displayText($anime['genres']); ?> </p>
		<?php } ?>

		<p> <?php echo displayText($anime['reason']); ?> </p>

		<a href="landing.php?id=<?php echo urlencode($anime['animeid']); ?>">View anime</a>
	</div>
<?php } ?>

</div>


<?php } ?>

</main>

</body>
</html>
