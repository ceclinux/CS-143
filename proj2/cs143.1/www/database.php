<?php




function dbConnect() {

	$username = "cs143";	
	$password = "";
	$database = "Test";
	$mysql_server = "localhost";


	$db = mysqli_connect($mysql_server, "$username", $password, $database) or die("Could not connect to database!");



	return $db;
 
}



?>
