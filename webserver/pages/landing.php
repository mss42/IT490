<?php
session_start();

//checks to see if user is logged in by looking at username
if (!isset($_SESSION['username'])) {
	header("Location: login.php");
	exit();
}

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

//sends request to queue an return reply

function sendToQueue($server, $request) {
	$oldDir = getcwd();
	chdir( __DIR__ . '/../../RabbitMQ');
	$client = new rabbitMQClient("testRabbitMQ.ini", $server);
	chdir($oldDir);

	return $client->send_request($request);
}

$message= '';
$heading= '';
$results= array();
$detail= null;
$detailNotes= '';

//queue is for what the user types and ID is for when user clicks an anime
$queue = trim($_GET['queue'] ?? '');
$id = trim($_GET['id'] ?? '');

try {
	//if anime clicked get info on it
	if ($id !== '') {
		if (!ctype_digit($id)) {
			$message = 'Invailed anime';
		}

		else {
			//checks to see if anime is cached already
			$dbReply = sendToQueue("animeServer", array("type" => "getAnime", "animeid" => $id));

			if (isset($dbReply['returnCode']) && $dbReply['returnCode'] == 0) {

				$row = $dbReply['anime'] ?? array();
				$detail = array("title" => $row['title'] ?? 'Untitled', "image" => $row['main_pic'] ??'', "score" => $row['score'] ?? '', "synopsis" => $row['synopsis'] ?? '');
				$detailNotes = 'Loaded from database';
			}

			elseif (isset($dbReply['returnCode']) && $dbReply['returnCode'] == 1) {
				//not in database: get from API
				$apiReply = sendToQueue("searchServer", array("type" => "details", "animeid" => $id));				      
				if (isset($apiReply['returnCode']) && $apiReply['returnCode'] == 0) {
					$detail = $apiReply['anime'];
					$detailNotes = 'Loads from the API and saves it';

//save in DB
	sendToQueue("animeServer", array( "type" => "addAnime", "animeid" => $detail['animeid'], "url" => $detail['url'], "title" => $detail['title'], "synopsis" => $detail['synopsis'], "image" => $detail['image'], "score" => $detail['score'])); 
}
 
	else {
	$message = $apiReply['message'] ?? 'Could not load';
	} 
}
		else {
		$message =$dbReply['message'] ?? 'Error from backend';
		}
	}
}

//search anime

if ($queue !== '') {
	$response = sendToQueue("searchServer", array("type" => "search", "query" => $queue));
}
else {
	$response = sendToQueue("searchServer", array("type" => "top"));
}

if (!isset($response['returnCode']) || $response['returnCode'] != 0) {
	$message = $response['message'] ?? 'Search failed';
}
elseif (count($response['results']) == 0) {
	$message = 'No results were found for "' . $queue . '"';
}
else {
	$results = $response['results'];
	if ($queue !== '') {
		$heading = count($results) . ' results for "' . $queue . '"';
	}
		else {
		$heading = 'Top anime';
		}
	}
}

catch (Throwable $error) {
	//broker not up
	$message = 'Could not reach server';
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login Successful</title>
	<link rel="stylesheet" href="main.css">
</head>
<body>
<h1>Welcome in <?php echo htmlspecialchars($_SESSION['username']); ?> <h1>

<!--	<form id="search_form" class="search_bar">
		<input type="text" id="search_input" placeholder="Search" maxlength="100" required />
		<button type="submit">Search</button>

</form>

-->
<!-- search bar -->
<form method="GET" action="landing.php" class="search_bar">
	<input type="text" name="queue" value="<?php echo htmlspecialchars($queue); ?>" placeholder="Search anime" maxlength="100" required />
	<button type="submit">Search</button>

</form>

	<!-- error or no result -->
	<?php if ($message !== '') { ?>
		<div id="search_status"> <?php echo htmlspecialchars($message); ?> </div>
	<?php } ?>

	<!-- info on anime when clicked -->
	<?php if ($detail !== null) { ?>
		<div id="detail_panel">
			<?php if (!empty($detail['image'])) { ?>
				<img src="<?php echo htmlspecialchars($detail['image']); ?>" alt="<?php echo htmlspecialchars($detail['title']); ?>">
			<?php } ?>
			<h2><?php echo htmlspecialchars($detail['title']); ?> </h2>
<p>
<?php 
if ($detail['score']) {	
	echo 'Rating ' . htmlspecialchars($detail['score']);
}
else {
	echo "Ther is no score yet";
}
?>
</p>

<p>
<?php
if ($detail['synopsis']) {
	echo htmlspecialchars($detail['synopsis']);
}
else {
	echo 'There is no synopsis available';
}
?>
</p>

<small><?php echo htmlspecialchars($detailNotes); ?></small>
</div>

<?php } ?>

	<!-- list of headings above animes -->
	<?php if($heading !== '') { ?>
		<div id="search_status"><?php echo htmlspecialchars($heading); ?> </div>
	<?php } ?>


	<!-- anime cards when clicked reloads page -->
	
	<div class="search_results">
		<?php foreach ($results as $anime) { ?>
			<div class="result_card">
			<a href="landing.php?id=<?php echo urlencode($anime['animeid']); ?>&amp;queue=<?php echo urlencode($queue); ?>">
		
		<?php  if (!empty($anime['image'])) { ?>
		<img src="<?php echo htmlspecialchars($anime['image']); ?>" alt="<?php echo htmlspecialchars($anime['title']); ?>">
		<?php } ?>
		
		<h3><?php echo htmlspecialchars($anime['title']); ?></h3>
		

		<?php
		//
		
		$info = array();
		if (!empty($anime['type'])) {
			$info[] = $anime['type'];
		}

		if (!empty($anime['year'])) {
			$info[] = $anime['year'];
		}
		if (!empty($anime['episodes'])) {
			$info[] = $anime['episodes'];
		}
		if (!empty($anime['score'])) {
			$info[] = 'Rating ' . $anime['score'];
		}
?>

		<p><?php echo htmlspecialchars(implode('|', $info)); ?></p>
		
		</a>
	</div>
	<?php } ?>
</div>


</body>
</html>


