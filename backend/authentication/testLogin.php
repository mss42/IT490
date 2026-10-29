<?php
require_once('login.php.inc');
$login = new loginDB();
$result = $login-> validateLogin("test", "4321");
var_dump($result);
?>
