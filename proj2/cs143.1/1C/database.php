<?php




function dbConnect() {

	$username = "cs143";	
	$password = "";
<<<<<<< local
	$database = "Test";
=======
	$database = "CS143";
>>>>>>> other
	$mysql_server = "localhost";


	$db = mysqli_connect($mysql_server, "$username", $password, $database) or die("Could not connect to database!");



	return $db;
 
}



?>
