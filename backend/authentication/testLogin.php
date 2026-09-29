<?php
require_once('login.php.inc');
$login = new loginDB();
$result = $login-> validateLogin("testUser", "12345");
var_dump($result);
?>
