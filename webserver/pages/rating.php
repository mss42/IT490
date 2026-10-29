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

try {
	//save review after submited
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$rating = $_POST['rating'] ?? '';
		$review = trim($_POST['review'] ?? '');

	//function to check if the rating is between 1-5
	if (!ctype_digit($rating) || $rating < 1 || $rating > 5) {
		$message ="Rate the anime from a scale 1 to 5.";
	}
	else {
		//sends it to the backend
		$reply = sendToQueue("reviewServer", array( "type" => "addReview", "animeid" => $animeid, "username" => $_SESSION['username'], "rating" => (int)$rating, "review" => $review));

		//redirect if review was saved
		if (isset($reply['returnCode']) && $reply['returnCode'] == 0) {
			header("Location:rating.php?animeid=" . $animeid);
			exit();
		}

		$message = "Your review was not saved";
	}
}

		
	//get the reviews from the anime
	$reply = sendToQueue("reviewServer", array( "type" => "getReviews", "animeid" => $animeid));

	//store the results (review,average rating, total number
	if (isset($reply['returnCode']) && $reply['returnCode'] == 0) {
		$reviews = $reply['reviews'];
		$average = $reply['average'];
		$total = $reply['total'];
	}
	else {
		$message = "Could not load the reviews";
	}
}

catch (Throwable $error) {
	//broker not up
	$message = "Could not reach server ";
}



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

	<!-- display error messages -->
	<?php if ($message != '') { ?>
		<p><?php echo htmlspecialchars($message); ?></p>
	<?php } ?>

	<!-- display number of reviews and the average rating -->
	<?php if ($total > 0) { ?>
		<p>Average Rating: <strong><?php echo htmlspecialchars($average); ?> / 5</strong> (<?php echo htmlspecialchars($total); ?> reviews)</p>
	<?php } else { ?>	
		<p>There are no reviews yet</p>
	<?php } ?>

	<!-- Shows the reviews from other users -->
	<?php foreach ($reviews as $item) { ?>
		<div class="forum_post">
			<p><?php echo htmlspecialchars($item['username']); ?></p>
			<p> - <?php echo htmlspecialchars($item['rating']); ?> / 5 stars</p>
			<p><?php echo htmlspecialchars($item['review']); ?></p>

		</div>
	<?php } ?>

	<h2>Your Review</h2>
	
	<form method="Post" class="forum_form">

		<select name="rating" required>
			<option value="">Choose a rating </option>
			<option value="5">5 - Great</option>
			<option value="4">4 - Good</option>
			<option value="3">3 - Okay</option>
			<option value="2">2 - Poor</option>
			<option value="1">1 - Bad</option>
		</select>

		<!-- enter a review -->
		<textarea name="review" placeholder="Write review here " rows="4"></textarea>

		<!-- Submit button for rating and review -->
		<button type="submit">Submit Review</button>
	</form>
	
	<!--link back to landing -->
	<p><a href="landing.php">Back to Landing page</a></p>
</body>
</html>
	
			
