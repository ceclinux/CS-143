<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<link href="style.css" rel="stylesheet" type="text/css" />

<body>

<div id="contents">
<div id="left">

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

	// look at movie
	$mov = "SELECT title, rating, year, company 
		FROM Movie M
		WHERE id = '$input'";

	// look for director
	$dir = "SELECT first, last, dob, id
		FROM Director D, MovieDirector MD
		WHERE MD.mid = '$input' AND MD.did = D.id";

	// look for actors
	$act = "SELECT first, last, dob, id, role
		FROM Actor A, MovieActor MA
		WHERE MA.mid = '$input' AND MA.aid = A.id ORDER BY first, last";

	// genre
	$gen = "SELECT genre FROM MovieGenre WHERE mid = '$input'";

	// review
	$rev = "SELECT name, time, mid, rating, comment
		FROM Review WHERE mid = '$input'";

	// ratings
	$rat = "SELECT AVG(rating) FROM Review WHERE mid = '$input' 
		GROUP BY mid";

	$movie = mysql_query($mov, $db_connection);
	$director = mysql_query($dir, $db_connection);
	$actor = mysql_query($act, $db_connection);
	$genre = mysql_query($gen, $db_connection);
	$review = mysql_query($rev, $db_connection);
	$rating = mysql_query($rat, $db_connection);

	if ($movie && $director && $genre){
		$m = mysql_fetch_row($movie);
		$d = mysql_fetch_row($director);
		echo "<h2>Movie info on " .$m[0]. "</h2>";
		echo "<p>";
		echo "Title: <b>$m[0]</b><br/>";
		echo "Production Company: <b>$m[3]</b><br/>";
		echo "MPAA Rating: <b>$m[1]</b><br/>";
		echo "Director: <b> ";
		echo "<a href='./directors.php?id=$d[3]'>$d[0] $d[1]</a></b><br/>";
		echo "Genre: <b>";
		while ($g = mysql_fetch_row($genre)){
			echo "$g[0] ";
		}
		echo "</b><br/>";
		echo "Average rating: <b> ";
		if ($rating && mysql_num_rows($rating) > 0) {
			$avgr = mysql_fetch_row($rating);
			if ($avgr[0] > 0)	
				echo "$avgr[0] stars.</b>";
			else echo "No ratings yet! </b>";
		} else { echo "No ratings yet! </b>";}	
		echo "<br/><br/>";
	}
	else {
		echo "Invalid id.";
	}

	echo "<b>Cast: </b><br>";
	if ($actor){
		while ($a = mysql_fetch_row($actor)){
			// for each element in that row
			echo "<a href = './actors.php?id=$a[3]'>";
			for($x=0; $x<2; $x++){
				echo " $a[$x]";
			}
			echo "</a> ";
			echo "as \"" .$a[4]. "\"<br/>";

			//echo "DOB: ";
			//echo "" .$a[2]. " <br/>";
		}
	}	

	echo "<h2> Reviews: </h2>";
	if ($review){
		while($r = mysql_fetch_row($review)){
			echo "<p> <b>$r[0]</b> rated this movie <u>$r[3]</u> stars";
			echo " on " .$r[1]. ". <br/>";
			echo "Comment: " .$r[4]. "</p>";	
		}
	} else { echo "<p> No reviews currently! </p>" ; }
?>
<form action="./comment.php?id=" method="GET">
<!--FIXME-->
<p>
<input type="hidden" name="id" value ="<?php echo "" .$input. ""; ?>"/>
<input type="submit" value="Add Review!"?></p>
</form>
<?php

	// close database
	mysql_close($db_connection);
}
?>

<?php
// show results of actors that start with the letter
if($_GET[title]){
	$ttl = $_GET[title];

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db_connection);


	$query = "SELECT title, id FROM Movie
		WHERE title LIKE '$ttl%' ORDER BY title";

	echo "<h2>Movies starting with letter <u>".$ttl. "</u></h2>";
	$result = mysql_query($query, $db_connection);

	echo "<p>";
	if ($result)
		while($row = mysql_fetch_row($result)){
			echo "<a href = './movies.php?id=$row[1]'>";
			echo "" .$row[0]. " ";
			echo "</a><br/>";
		}
	echo "</p>";

	// close databse
	mysql_close($db_connection);
}

?>




<?php
// PHP to display letters at the bottom
echo "<h3>View Movies (by Title): </h3>";
$some = array(A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z);

foreach ($some as $l){
	letters($l);
}

function letters($letter)
{
	echo " <a href=./movies.php?title=$letter>" .$letter. "</a> ";
	if($letter != Z){
		echo "|";
	}
}
?>



<p><p><p>
</body>
</html>
