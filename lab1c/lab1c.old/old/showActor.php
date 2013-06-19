
<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<body>

<body>
Search an Actor/ Director/ Movie

<p>
<form method="GET">
<input type="text" name="search" action="./search.php" /><input type="submit" value="Search" />
</form>
</p>


<?php
// get the input
if($_GET["id"]){
	$input = $_GET["id"];

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db_connection);

	// query for information about the actor
	$query = "SELECT first, last, sex, dob, dod
		FROM Actor
		WHERE id = '$input'";

	// query for movie information
	$movies = "SELECT title, role, mid, year
		FROM MovieActor MA, Movie M
		WHERE MA.aid = '$input' AND MA.mid = M.id ORDER BY year DESC";

	// query for director

	$check = mysql_query($query, $db_connection);
	$mov = mysql_query($movies, $db_connection);

	// show information about the actor
	echo "<b> Actor Information: </b>";
	if ($check){
		$c = mysql_fetch_row($check);
		echo "<p>";
		echo "Name: <b>" .$c[0]. " " .$c[1]. "</b><br/>";
		echo "Gender: <b>" .$c[2]. "</b><br/>";
		echo "Date of Birth: <b>" .$c[3]. "</b><br/>";
		if ($c[2] != "") // FIXME 
			echo "Date of Death: <b>" .$c[4]. "</b><br/>";
		else { echo "Date of Death: N/A <br>"; }

	} else { echo "Invalid input. "; }

	if ($mov){
		echo "<p><p>";
		echo "<b> Filmography: </b>";
		echo "<p>";
		while ($r = mysql_fetch_row($mov)){
			// for each element in that row
			echo "<a href = './showMovie.php?id=$r[2]'>";
			echo "" .$r[0]. " ";
			echo "</a>";
			echo "as \"" .$r[1]. "\"";
			echo " - " .$r[3]. ". <br/>";
		}
	} else { echo " Actor is not in a Movie! "; }






	// close database
	mysql_close($db_connection);
}
?>

</body>
</html>
