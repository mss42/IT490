#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('authentication/session.php.inc');

//php request manager to submit login info, checks if user already exists

function requestManager($request)
{
        print_r($request);

        if (!isset($request['type']))
        {
                return array(
                        "returnCode" => '1',
                        "message" => "Missing request type womp womp"
                );

        }

        //accessing database

        if ($request['type'] == "login")
        {
                $db = new mysqli('100.105.60.109', 'dbUser', '12345', 'authenticationdb' );

        }

//checking if databse already has exisitng user

        try{
        if ($db->connect_error) {
                return array(
                        "returnCode" => 1,
                        "message" => "Database connectivty FAILED try again LMAOO"
                );
        }

        // Prepare SQL Statement, then set parameters, then execute the statement.

        $chkUser = $db->prepare("SELECT userid, username, password FROM users WHERE username = ?");

        $chkUser->bind_param('s',$request['username']);

        $chkUser->execute();

        $result = $chkUser->get_result();



        //checks to see if there credentials exist

        if ($result->num_rows > 0) {

                $existingUser = $result->fetch_assoc();
		$userid =  $existingUser['userid'];

	//does password match
                 if ($existingUser['password'] == $request['password']){
				return array(
                                        "returnCode" => 0,
                                        "message" => "Correct Password"
                                );

				//$session = new sessionDB();
				//$sessionResult = $session-> createSession($userid);
                        }

                }
        }
        catch(mysqli_sql_exception $exception)
        {
                echo "DB Error: " . $exception->getMessage() . PHP_EOL;
                return array(
                        "returnCode"=> 1,
                        "message" => "database error..."
                );
        }
}




$server = new rabbitMQServer("testRabbitMQ.ini","authServer");

echo "Waiting for login information..." . PHP_EOL;

$server->process_requests('requestManager');

?>
