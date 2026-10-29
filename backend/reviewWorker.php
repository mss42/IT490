
#!/usr/bin/php
<?php

require_once __DIR__ . '/../RabbitMQ/rabbitMQLib.inc';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection
function getReviewDB() {
    return new mysqli(
        '127.0.0.1',
        'animeUser',
        '12345',
        'animedb'
    );
}

// Validate session and gets userid.
function getSessionUserID($sessionID) {

    if (!is_string($sessionID) || $sessionID === '') {
        return null;
    }

    $db = new mysqli('127.0.0.1','dbUser','12345','authenticationdb');

    $stmt = $db->prepare(
        "SELECT userid
         FROM sessions
         WHERE sessionid = ?
         AND expires_at > NOW()
         LIMIT 1"
    );

    $stmt->bind_param("s", $sessionID);
    $stmt->execute();

    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $db->close();

    return $row ? (int)$row['userid'] : null;
}

function requestManager($request) {

    $type = $request['type'] ?? '';
    echo "Request: " . $type . PHP_EOL;

    try {

        //add Review
        if ($type === 'addReview') {
            $sessionID = $request['sessionID'] ?? '';
            $animeid = $request['animeid'] ?? '';
            $rating = $request['rating'] ?? null;
            $review = $request['review'] ?? '';

            //check user authenticated
            $userid = getSessionUserID($sessionID);

            if ($userid === null) {
                return array(
                    "returnCode" => 1,
                    "message" => "Invalid or expired session"
                );
            }

            // check anime ID
            if (!ctype_digit((string)$animeid) || (int)$animeid <= 0) {
                return array(
                    "returnCode" => 1,
                    "message" => "Invalid anime ID"
                );
            }

            // Validate rating
            if (filter_var($rating,
                    	   FILTER_VALIDATE_INT,
                    	   array("options" => array("min_range" => 1,"max_range" => 5))
                	   ) === false
            ) {
                return array(
                    "returnCode" => 1,
                    "message" => "Rating must be 1 to 5"
                );
            }

            // Validate review text
            if (!is_string($review) ||
                mb_strlen($review) > 500) {
                return array(
                    "returnCode" => 1,
                    "message" => "Review exceeds 500 characters"
                );
            }

            $animeid = (int)$animeid;
            $rating = (int)$rating;
            $review = trim($review);

            $db = getReviewDB();

            $stmt = $db->prepare("INSERT INTO reviews
                    (anime_id, userid, rating, review)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    rating = VALUES(rating),
                    review = VALUES(review),
                    updated_at = NOW()"
            );

            $stmt->bind_param("iiis",$animeid,$userid,$rating,$review);
            $stmt->execute();
            $stmt->close();
            $db->close();

            echo "Review saved for user $userid, anime $animeid"
                . PHP_EOL;

            return array(
                "returnCode" => 0,
                "message" => "Review saved"
            );
        }

        //getReviews
        if ($type === 'getReviews') {
            $animeid = $request['animeid'] ?? '';

            if (!ctype_digit((string)$animeid) ||(int)$animeid <= 0) {
                return array(
                    "returnCode" => 1,
                    "message" => "Invalid anime ID"
                );
            }

            $animeid = (int)$animeid;
	    $db = getReviewDB();

            // gets max 50 reviews
            $stmt = $db->prepare("SELECT userid, rating, review, created_at
                 		FROM reviews
                 		WHERE anime_id = ?
                 		ORDER BY created_at DESC
                 		LIMIT 50");

            $stmt->bind_param("i", $animeid);
            $stmt->execute();

            $reviews = $stmt->get_result()
                            ->fetch_all(MYSQLI_ASSOC);

            $stmt->close();

            // Calculate avg rating
            $stmt = $db->prepare("SELECT
                    AVG(rating) AS average,
                    COUNT(*) AS total
                 FROM reviews
                 WHERE anime_id = ?");

            $stmt->bind_param("i", $animeid);
            $stmt->execute();

            $stats = $stmt->get_result()->fetch_assoc();

            $stmt->close();
            $db->close();

            return array(
                "returnCode" => 0,
                "reviews" => $reviews,
                "average" => round((float)$stats['average'], 1),
                "total" => (int)$stats['total']);
        }
        return array(
            "returnCode" => 1,
            "message" => "Unknown request type"
        );

    } catch (Throwable $error) {
        error_log("Review worker: " . $error->getMessage());
        return array(
            "returnCode" => 1,
            "message" => "Server error"
        );
    }
}

chdir(__DIR__ . '/../RabbitMQ');

$server = new rabbitMQServer("testRabbitMQ.ini","reviewServer");

echo "Waiting for review requests..." . PHP_EOL;

$server->process_requests('requestManager');

?>
