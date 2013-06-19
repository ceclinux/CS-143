
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
	$rawinput = $_GET["search"];
	$input = explode(" ", $rawinput);

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}

	$db = array(Actor, Director, Movie);

	mysql_select_db("CS143", $db_connection);


	echo "<p><b> Actors:</b> </p>";

	// show the actors that match
	foreach ($input as $v){
		$query = "SELECT first, last, dob, id FROM Actor WHERE first LIKE '$v' OR
                    last LIKE '$v' ORDER BY first, last";
		
		$result = mysql_query($query, $db_connection);

		if ($result){
			while ($row = mysql_fetch_row($result)){
				echo "<a href = './showMovie.php?id=$row[1]'>";
				for($x=0; $x<3; $x++){
                        echo "" .$row[$x]. " ";
                    }
                    echo "</a><br/>";
			}
		}
	}

	foreach ($db as $val){
		foreach ($input as $v){

			// search actors
			if ($val == Actor || $val == Director)
				$query = "SELECT first, last, dob, id FROM $val WHERE first LIKE '%$input%' OR
					last LIKE '%$input%' ORDER BY first";
			else 
				$query = "SELECT title, id FROM $val WHERE title LIKE '%$input%'";
			// select database
			mysql_select_db("CS143", $db_connection);

			//$query = $sanquery;
			//echo "Your query: ".$query." <br/>";
			echo "<p><b>Searching match in $val:</b></p>";

			$result = mysql_query($query, $db_connection);

			$x = 0;
			if ($result){
				// read a row
				while ($row = mysql_fetch_row($result)){
					// for each element in that row
					if ($val == Movie)
						echo "<a href = './showMovie.php?id=$row[1]'>";
					elseif ($val == Actor)
						echo "<a href = './showActor.php?id=$row[3]'>"; 
					else echo "<a href = './ShowDirector.php?id=$row[3]'>";
					$y = 3;
					if ($val == Movie){$y = 1;}
					for($x=0; $x<$y; $x++){
						echo "" .$row[$x]. " ";
					}
					echo "</a><br/>";
				}
			}
		}
	}
	// close database
	mysql_close($db_connection);
}
?>

</body>
</html>
