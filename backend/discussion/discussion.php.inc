<?php

class discussionDB
{
private $conn;

public function __construct()
{
        $this->conn = new mysqli("127.0.0.1","animeUser","12345","animedb");

        if ($this->conn->connect_errno != 0)
        {
                echo "Error connecting to database: ".$this->conndb->connect_error.PHP_EOL;
                exit(1);
        }
        echo "correctly connected to database".PHP_EOL;
}

public function getThreads($animeid)
{
	 $query = "SELECT
            t.thread_id,
            t.anime_id,
            t.title,
            t.created_at,
            u.username,
	    (
                SELECT COUNT(*)
                FROM discussion_replies r
                WHERE r.thread_id = t.thread_id
            ) AS reply_count
        FROM discussion_threads t
        JOIN authenticationdb.users u
            ON t.userid = u.userid
        WHERE t.anime_id = ?
        ORDER BY t.created_at DESC
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $animeid);
    $stmt->execute();
    $result = $stmt->get_result();
    $threads = [];

    while ($row = $result->fetch_assoc()) {
        $threads[] = [
            "category" => $row["anime_id"],
            "id" => $row["thread_id"],
            "title" => $row["title"],
            "author" => $row["username"],
            "created" => $row["created_at"],
            "count" => $row["reply_count"]
        ];
    }

    $stmt->close();

    return $threads;
}

public function getThread($threadid)
{
    //get discussion post
    $query = "
        SELECT
            t.thread_id,
            t.anime_id,
            t.title,
            t.content,
            t.created_at,
            u.username
        FROM animedb.discussion_threads t

        JOIN authenticationdb.users u
            ON t.userid = u.userid

        WHERE t.thread_id = ?
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $threadid);
    $stmt->execute();

    $result = $stmt->get_result();
    $thread = $result->fetch_assoc();

    $stmt->close();

    // Thread does not exist
    if (!$thread) {
        return null;
    }

    // Retrieve replies for this thread
    $query = "
        SELECT
            r.reply_id,
            r.content,
            r.created_at,
            u.username
        FROM animedb.discussion_replies r

        JOIN authenticationdb.users u
            ON r.userid = u.userid
        WHERE r.thread_id = ?

        ORDER BY r.created_at ASC, r.reply_id ASC
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $threadid);
    $stmt->execute();
    $result = $stmt->get_result();

    $replies = [];

    while ($row = $result->fetch_assoc()) {
        $replies[] = [
            "id" => $row["reply_id"],
            "author" => $row["username"],
            "content" => $row["content"],
            "created" => $row["created_at"]
        ];
    }

    $stmt->close();

    // Return post and replies
    return [
        "title" => $thread["title"],
        "author" => $thread["username"],
        "created" => $thread["created_at"],
        "text" => $thread["content"],
        "replies" => $replies
    ];
}



public function createThread($animeid, $userid, $title, $content)
{
    $title = trim($title);
    $content = trim($content);

    if ($title === "" || $content === "") {
        throw new InvalidArgumentException(
            "Thread title and content cannot be empty"
        );
    }
    $query = "INSERT INTO animedb.discussion_threads(anime_id, userid, title, content)
						VALUES (?, ?, ?, ?)";

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param(
        "iiss",
        $animeid,
        $userid,
        $title,
        $content
    );

    $stmt->execute();

    $threadid = $this->conn->insert_id;

    $stmt->close();

    return $threadid;
}


public function createReply($threadid, $userid, $content)
{
    $content = trim($content);

    if ($content === "") {
        throw new InvalidArgumentException(
            "Reply content cannot be empty"
        );
    }

    $query = "INSERT INTO animedb.discussion_replies(thread_id, userid, content)
        VALUES (?, ?, ?)";

    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("iis",$threadid,$userid,$content);

    $stmt->execute();
    $replyid = $this->conn->insert_id;
    $stmt->close();

    return $replyid;
}

public function getSessionUser($sessionid)
{
    $query = "SELECT userid
        FROM authenticationdb.sessions
        WHERE sessionid = ?
        AND expires_at > NOW()";

    $stmt = $this->conn->prepare($query);

    $stmt->bind_param("s", $sessionid);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return null;
    }

    return (int) $row["userid"];
}

public function getCreatorEmail($threadid)
{
    $query = "SELECT u.email, t.userid FROM animedb.discussion_threads t
		JOIN authenticationdb.users u
		ON u.userid = t.userid
		WHERE t.thread_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $threadid);
    $stmt->execute();

    $result = $stmt->get_result();
    $creator = $result->fetch_assoc();
    $stmt->close();

    return $creator;
}
}
