<?php
require_once('session.php.inc');
$session = new sessionDB();
$result = $session-> createSession("1");
var_dump($result);
?>
