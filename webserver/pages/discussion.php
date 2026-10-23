<?php
session_start();

//checks if user is logged in
if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
}

require_once __DIR__ . '/../../RabbitMQ/rabbitMQLib.inc';

//send request to rabbitmq and return reply
function sendToQueue($server, $request) {
        $oldDir = getcwd();
        chdir(__DIR__ . '/../../RabbitMQ');

        try {
                $client = new rabbitMQClient("testRabbitMQ.ini", $server);
                return $client->send_request($request);
        }
        finally {
                chdir($oldDir);
        }
}

//display values safely in html
function displayText($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$message = '';
$threads = array();
$thread = null;

//animeid identifies which anime the discussions belong to
$animeid = trim((string)($_GET['animeid'] ?? $_POST['animeid'] ?? ''));
$threadid = trim((string)($_GET['threadid'] ?? $_POST['threadid'] ?? ''));

//validate anime id
if ($animeid === '' || !ctype_digit($animeid) || (int)$animeid <= 0) {
        die("Invalid anime ID");
}

//validate thread id if provided
if ($threadid !== '' && (!ctype_digit($threadid) || (int)$threadid <= 0)) {
        die("Invalid thread ID");
}

//create a CSRF token for posting forms
if (!isset($_SESSION['discussion_csrf'])) {
        $_SESSION['discussion_csrf'] = bin2hex(random_bytes(32));
}

//handle new discussions and replies
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $action = $_POST['action'] ?? '';
        $content = trim($_POST['content'] ?? '');

        if (!hash_equals($_SESSION['discussion_csrf'], $_POST['csrf_token'] ?? '')) {
                $message = "Invalid form submission";
        }

        elseif ($action != 'createThread' && $action != 'createReply') {
                $message = "Invalid request";
        }

        elseif ($content === '') {
                $message = "Content cannot be empty";
        }

        elseif (empty($_SESSION['sessionID'])) {
                $message = "Your login session is missing. Please log in again.";
        }

        else {
                try {
                   $sessionid = $_SESSION['sessionID'];

                   //create a new discussion
                   if ($action == "createThread") {

                           $title = trim($_POST['title'] ?? '');
                            if ($title === '') {
                                   $message = "Discussion title cannot be empty";
                           }
                           else {
                                   $request = array(
                                           "type" => "createThread",
                                           "animeid" => (int)$animeid,
                                           "sessionID" => $sessionid,
                                           "title" => $title,
                                           "content" => $content
                                   );

                                   $response = sendToQueue("discussionServer", $request);

                                   if (isset($response['returnCode']) && $response['returnCode'] == 0) {
                                           $newthreadid = $response['threadid'];

                                           header("Location: discussion.php?animeid=" . urlencode($animeid) . "&threadid=" . urlencode($newthreadid));
                                           exit();
                                   }else {
                                           $message = $response['message'] ?? "Could not create discussion";
                                   }
                           }
                   }

                   //reply to an existing discussion
                   if ($action == "createReply") {

                           if ($threadid === '') {
                                  $message = "Missing thread ID";
                           } else {
                                  $request = array(
                                           "type" => "createReply",
                                           "threadid" => (int)$threadid,
                                           "sessionID" => $sessionid,
                                           "content" => $content
                                   );

                                   $response = sendToQueue("discussionServer", $request);

                                   if (isset($response['returnCode']) && $response['returnCode'] == 0) {
                                           header("Location: discussion.php?animeid=" . urlencode($animeid) . "&threadid=" . urlencode($threadid));
                                           exit();
                                   }else {
                                           $message = $response['message'] ?? "Could not create reply";
                                   }
                           }
                   }
           }
           catch (Throwable $error) {
                   $message = "Could not reach discussion server";
           }
   }
}

//load discussions from backend
try {

        //get a specific thread and its replies
        if ($threadid !== '') {

                $response = sendToQueue("discussionServer", array(
                        "type" => "getThread",
                        "threadid" => (int)$threadid
                ));

                if (isset($response['returnCode']) && $response['returnCode'] == 0) {
                        $thread = $response['thread'] ?? null;
                }else {
                        $message = $response['message'] ?? "Could not load discussion";
                }
        }

        //get all threads for an anime
        else {

                $response = sendToQueue("discussionServer", array(
                        "type" => "getThreads",
                        "animeid" => (int)$animeid
                ));

                if (isset($response['returnCode']) && $response['returnCode'] == 0) {
                        $threads = $response['threads'] ?? array();
                }
                else {
                        $message = $response['message'] ?? "Could not load discussions";
                }
        }
}
catch (Throwable $error) {
        $message = "Could not reach discussion server";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Anime Discussions</title>
        <link rel="stylesheet" href="main.css">
</head>

<body>
<h1>Anime Discussions</h1>

<p>
        <a href="landing.php?id=<?php echo urlencode($animeid); ?>">
                Back to Anime
        </a>
</p>

<?php if ($message !== '') { ?>
        <p><?php echo displayText($message); ?></p>
<?php } ?>

<?php if ($threadid === '') { ?>
        <!-- display all discussion threads -->

        <h2>Discussions</h2>
        <?php if (count($threads) == 0) { ?>
                <p>No discussions have been posted yet.</p>
        <?php } ?>

        <?php foreach ($threads as $discussion) { ?>

                <div class="discussion-thread">

                        <h3>
                           <a href="discussion.php?animeid=<?php echo urlencode($animeid); ?>&amp;threadid=<?php echo urlencode($discussion['id']); ?>">
                                   <?php echo displayText($discussion['title']); ?>
                           </a>
                        </h3>

                        <p>
                                Posted by <?php echo displayText($discussion['author']); ?>
                        </p>

                        <p>
                                <?php echo displayText($discussion['count']); ?> replies
                        </p>

                        <small>
                                <?php echo displayText($discussion['created']); ?>
                        </small>

                </div>

        <?php } ?>


        <!-- create new discussion -->

        <h2>Create a Discussion</h2>

        <form method="POST" action="discussion.php?animeid=<?php echo urlencode($animeid); ?>">

                <input type="hidden" name="action" value="createThread">
                <input type="hidden" name="animeid" value="<?php echo displayText($animeid); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo displayText($_SESSION['discussion_csrf']); ?>">

                <p>
                        <input type="text" name="title"
                               placeholder="Discussion title"
                               maxlength="255" required>
                </p>

                <p>
                        <textarea name="content"
                                  placeholder="Write your discussion..."
                                  rows="6" required></textarea>
                </p>

                <button type="submit">Create Discussion</button>

        </form>


<?php } else { ?>

        <!-- display original post and replies -->

        <p>
                <a href="discussion.php?animeid=<?php echo urlencode($animeid); ?>">
                        Back to Discussions
                </a>
        </p>

        <?php if ($thread !== null) { ?>

                <div class="discussion-post">

                        <h2><?php echo displayText($thread['title']); ?></h2>

                        <p>
                                Posted by <?php echo displayText($thread['author']); ?>
                        </p>

                        <small><?php echo displayText($thread['created']); ?></small>

                        <p>
                                <?php echo nl2br(displayText($thread['text'])); ?>
                        </p>

                </div>


                <h2>Replies</h2>

                <?php if (count($thread['replies']) == 0) { ?>
                        <p>No replies yet.</p>
                <?php } ?>

                <?php foreach ($thread['replies'] as $reply) { ?>

                        <div class="discussion-reply">

                                <p>
                                   <strong><?php echo displayText($reply['author']); ?></strong>
                                </p>

                                <small>
                                   <?php echo displayText($reply['created']); ?>
                                </small>

                                <p>
                                   <?php echo nl2br(displayText($reply['content'])); ?>
                                </p>

                        </div>

                <?php } ?>

                <!-- reply to discussion -->
                <h2>Post a Reply</h2>

                <form method="POST" action="discussion.php?animeid=<?php echo urlencode($animeid); ?>&amp;threadid=<?php echo urlencode($threadid); ?>">

                        <input type="hidden" name="action" value="createReply">
                        <input type="hidden" name="animeid" value="<?php echo displayText($animeid); ?>">
                        <input type="hidden" name="threadid" value="<?php echo displayText($threadid); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo displayText($_SESSION['discussion_csrf']); ?>">

                        <p>
                                <textarea name="content"
                                          placeholder="Write your reply..."
                                          rows="5" required></textarea>
                        </p>

                        <button type="submit">Post Reply</button>

                </form>

        <?php } ?>

<?php } ?>

</body>
</html>
