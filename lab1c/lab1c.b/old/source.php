
<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<body>
Search an Actor/ Director/ Movie

<p>
<form method="GET">
<input type="text" name="search" /><input type="submit" value="Search" />
</form>
</p>

<?php
// get the input
if($_GET["search"]){
	$input = $_GET["search"];

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}

	$db = array(Actor, Director, Movie);

	foreach ($db as $val){
		// search actors
		if ($val == Actor || $val == Director)
			$query = "SELECT first, last, dob, id FROM $val WHERE last = '$input' OR
				first = '$input' ORDER BY first";
		else 
			$query = "SELECT title, id FROM $val WHERE title LIKE '%$input%'";
		// select database
		mysql_select_db("CS143", $db_connection);

		//$query = $sanquery;
		echo "Your query: ".$query." <br/>";
		echo "<h1>Searching match in $val:</h1>";

		$result = mysql_query($query, $db_connection);

		$x = 0;
		if ($result){
			// read a row
			while ($row = mysql_fetch_row($result)){
				// for each element in that row
				echo "<a href = './showMovie.php?id=$row[1]'>";
				$y = 3;
				if ($val == Movie){$y = 1;}
				for($x=0; $x<$y; $x++){
					echo "" .$row[$x]. " ";
				}
				echo "</a><br/>";
			}
		}
	}
	// close database
	mysql_close($db_connection);
}
?>

</body>
</html>
