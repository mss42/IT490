<?php
                 $db = new mysqli('100.105.60.109', 'sessionUser', '12345', 'authenticationdb' );


        $sessionId = bin2hex(random_bytes(32));
	$user = 1;
        $date = new DateTime();
        $date->modify('+1 hour');
        $expires_at = $date->format('Y-m-d H:i:s');
        $addSession = $db->prepare("INSERT INTO sessions (sessionid, userid, expires_at)
        VALUES (?, ?, ?)");
        $addSession->bind_param('sis', $sessionId, $user, $expires_at);
        $addSession->execute();
        $addSession->close();
        $db->close();

?>
