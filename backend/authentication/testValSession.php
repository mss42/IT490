<?php
require_once('session.php.inc');
$session = new sessionDB();
$result = $session-> validateSession("no_session");
var_dump($result);
?>
