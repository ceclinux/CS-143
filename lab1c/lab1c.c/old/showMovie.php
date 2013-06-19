
<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<body>

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

	$query = "SELECT D.first, D.last, title, year, rating, company, genre
		FROM Movie M, Director D, MovieDirector MD, MovieGenre MG
		WHERE M.id = '$input' AND M.id = MD.mid AND M.id = MG.mid AND 
			MD.did = D.id";
	$check = mysql_query($query, $db_connection);

	/*echo "<h2> Movie Information: </h2>";
	if ($check){
		$c = mysql_fetch_row($check);
		echo "<p>";
		echo "Title: <b>" .$c[2]. "</b><br/>";
		echo "Production Company: <b>" .$c[5]. "</b><br/>";
		echo "MPAA Rating: <b>".$c[4]. "</b><br/>";
		echo "Director: <b>" .$c[0]. " " .$c[1]. "</b><br/>";
		echo "Genre: <b>" .$c[6]. "</b><br/>";
	} else { echo "Invalid input. "; }
*/
	// look at movie
	$mov = "SELECT title, rating, year, company 
		FROM Movie M
		WHERE id = '$input'";

	// look for director
	$dir = "SELECT first, last, dob
		FROM Director D, MovieDirector MD
		WHERE MD.mid = '$input' AND MD.did = D.id";

	// look for actors
	$act = "SELECT first, last, dob, id, role
		FROM Actor A, MovieActor MA
		WHERE MA.mid = '$input' AND MA.aid = A.id";

	// genre
	$gen = "SELECT genre FROM MovieGenre WHERE mid = '$input'";

	// review
	$rev = "SELECT name, time, mid, rating, comment
		FROM Review R, Movie M WHERE mid = M.id";

	$movie = mysql_query($mov, $db_connection);
	$director = mysql_query($dir, $db_connection);
	$actor = mysql_query($act, $db_connection);
	$genre = mysql_query($gen, $db_connection);
	$review = mysql_query($rev, $db_connection);

	echo "<h2>Movie information: </h2>";
	if ($movie && $director){
		$m = mysql_fetch_row($movie);
		$d = mysql_fetch_row($director);
		echo "<p>";
		echo "Title: <b>" .$m[0]. "</b><br/>";
		echo "Production Company: <b>" .$m[3]. "</b><br/>";
		echo "MPAA Rating: <b>".$m[1]. "</b><br/>";
		echo "Director: <b>".$d[0]. " " .$d[1]. "</b><br/>";
		echo "Genre: <b>";
		while ($g = mysql_fetch_row($genre)){
			echo "" .$g[0]. " ";
		}
		echo "</b><br/><br/>";
	}
	else {
		echo "Invalid id.";
	}

	echo "<b>Cast: </b><br>";
	if ($actor){
		while ($a = mysql_fetch_row($actor)){
			// for each element in that row
			echo "<a href = './showActor.php?id=$a[3]'>";
			for($x=0; $x<2; $x++){
				echo "" .$a[$x]. " ";
			}
			echo "</a>";
			echo "as \"" .$a[4]. "\"<br/>";

			//echo "DOB: ";
			//echo "" .$a[2]. " <br/>";
		}
	}	

	echo "<h2> Reviews: </h2>";
	if ($review){
		$r = mysql_fetch_row($review);
	}

	// close database
	mysql_close($db_connection);
}
?>

</body>
</html>
