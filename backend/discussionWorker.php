
#!/usr/bin/php
<?php
require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';
require_once __DIR__ . '/discussion/discussion.php.inc';

function requestManager($request){

        if (!isset($request['type'])){
                return array(
                        "returnCode" => 1,
                        "message" => "Missing request type"
                );
        }

//get all threads for an anime
if ($request['type'] == "getThreads"){
        try{
                if (!isset($request['animeid'])){
                        return array(
                                "returnCode" => 1,
                                "message" => "Missing anime ID"
                        );
                }

                $animeid = $request['animeid'];

                $discussion = new discussionDB();
                $threads = $discussion->getThreads($animeid);

                return array(
                        "returnCode" => 0,
                        "threads" => $threads
                );
        }
        catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, please try again"
                );
        }
}

//get discussion post and replies
if ($request['type'] == "getThread"){
        try{
                if (!isset($request['threadid'])){
                        return array(
                                "returnCode" => 1,
                                "message" => "Missing thread ID"
                        );
                }

                $threadid = $request['threadid'];

                $discussion = new discussionDB();
                $thread = $discussion->getThread($threadid);

                if ($thread === null){
                        return array(
                                "returnCode" => 1,
                                "message" => "Thread not found"
                        );
                }

                return array(
                        "returnCode" => 0,
                        "thread" => $thread
                );
        }
        catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, please try again"
                );
        }
}

//creates a new thread
if ($request['type'] == "createThread"){
        try{
                if (!isset($request['animeid'], $request['sessionID'],
                           $request['title'], $request['content'])){
                        return array(
                                "returnCode" => 1,
                                "message" => "Missing required information"
                        );
                }

                $animeid = $request['animeid'];
                $sessionid = $request['sessionID'];
                $title = $request['title'];
                $content = $request['content'];

                $discussion = new discussionDB();

                //get userid from the session
                $userid = $discussion->getSessionUser($sessionid);

                if ($userid === null){
                        return array(
                                "returnCode" => 1,
                                "message" => "Invalid or expired session"
                        );
                }

                $threadid = $discussion->createThread(
                        $animeid, $userid, $title, $content
                );

                return array(
                        "returnCode" => 0,
                        "threadid" => $threadid,
                        "message" => "Thread created successfully"
                );
        }
        catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (InvalidArgumentException $exception)
        {
                return array(
                        "returnCode" => 1,
                        "message" => $exception->getMessage()
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, please try again"
                );
        }
}

//create a reply
if ($request['type'] == "createReply"){
        try{
                if (!isset($request['threadid'], $request['sessionID'],
                           $request['content'])){
                        return array(
                                "returnCode" => 1,
                                "message" => "Missing required information"
                        );
                }

                $threadid = $request['threadid'];
                $sessionid = $request['sessionID'];
                $content = $request['content'];

                $discussion = new discussionDB();

                $userid = $discussion->getSessionUser($sessionid);

                if ($userid === null){
                        return array(
                                "returnCode" => 1,
                                "message" => "Invalid or expired session"
                        );
                }

                $replyid = $discussion->createReply(
                        $threadid, $userid, $content
                );

		//email notifications
		$creator = $discussion->getCreatorEmail($threadid);
		if($creator){
			$to = $creator['email'];
			$subject = "New Reply to Your Discussion Post";
			$body = "Log in and check the forum!";
			$headers = array("From" => "ds2329@njit.edu",
					"Conent-Type" => "text/plain; charset=UTF-9");
		try{
			$sent = mail($to, $subject, $body, $headers);
			if(!$sent){
				error_log("Discussion email did not send");
			}
		}catch(Throwable $error){
			error_log("Email Error: " . $error->getMessage());
		}

		}
                return array(
                        "returnCode" => 0,
                        "replyid" => $replyid,
                        "message" => "Reply created successfully"
                );
        }
        catch (mysqli_sql_exception $exception)
        {
                echo " DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Database error, please try again"
                );
        }
        catch (InvalidArgumentException $exception)
        {
                return array(
                        "returnCode" => 1,
                        "message" => $exception->getMessage()
                );
        }
        catch (Throwable $exception)
        {
                echo " Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode" => 2,
                        "message" => "Server error, please try again"
                );
        }
}
return array(
        "returnCode" => 1,
        "message" => "Unknown request type"
);

}

chdir(__DIR__ . '/../RabbitMQ');

$server = new rabbitMQServer("testRabbitMQ.ini", "discussionServer");

echo "Waiting for discussion request..." . PHP_EOL;

$server->process_requests('requestManager');

?>
