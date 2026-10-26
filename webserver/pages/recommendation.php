<?php
session_start();

//once again checks if user is logged in

if (!isset($_SESSION['username'])) {
	header("Location: login.php");
	exit();

}

//sample data lets hope kev messes with this anime 

$recommendation = [
	[
		"animeid" => 1,
		"title" => "Cowboy Bebop",
		"genres" => "Action, Sci-Fi",
		"reason" => "Matches your interest in Action anime"
	],
	[
		"animeid" => 20,
		"title" => "One Piece",
		"genres" => "Adventure, Action",
		"reason" => "Matches your interest in Adventure anime"
	],
	[
		"animeid" => 30,
		"title" => "Neon Genesis Evangelion",
		"genres" => "Drama, Sci-Fi",
		"reason" => "Matches your interest in Sci-Fi anime"
	]
];

// this function helps later on with displaying the animes samples
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

	<?php if (empty($recommendation)) { ?>

		<h2>No Recommendations yet</h2>

		<p> Come back when you have rated some anime so we can get a little taste of what you like </p>

		<a href="landing.php">Explore anime</a>
	<?php } else { ?>
		
	<div class="recommendation-grid">
		
		<?php foreach ($recommendation as $anime) { ?>

			<div class="recommendation-card">

		<h3> <?php echo displayText($anime['title']); ?> </h3>

		<p> <strong>Genres</strong> <?php echo displayText($anime['genres']); ?> </p>

		<p> <?php echo displayText($anime['reason']); ?> </p>

		<a href="landing.php?id=<?php echo urlencode($anime['animeid']); ?>">Anime<a/>
	</div>
<?php } ?>

</div>


<?php } ?>

</main>

</body>
</html>


