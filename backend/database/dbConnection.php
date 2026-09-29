#!/usr/bin/php
<?php

    $mydb = new mysqli('127.0.0.1', 'dbUser', '12345', 'authenticationdb');

    if ($mydb->errno != 0){
        echo "failed to connect to database: ". $mydb->error . PHP_EOL;
        exit();
    }

    echo "successfully connected to database".PHP_EOL;

    $query = "select * from users;";

    $response = $mydb->query($query);
    if($mydb->errno != 0){
        echo "failed to query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error; ".$mydb->error.PHP_EOL;
        exit(0);
    }
?>
