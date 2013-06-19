
<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<link href="style.css" rel="stylesheet" type="text/css" />

<body>

<div id="topbar">
<div id="TopSection">
<h1 id="sitename"><span><a href="#">IM</a></span><a href="#"><u>AFAKE</u></a>
<h1 id="sitename"><span>DB</span>
</h1>
<div id="topbarnav"> <span class="topnavitems"></span>

<form action="index.php" method="POST"><div class="searchform">
<label for="searchtxt">
Search Movies/Actors/Directors:
</label>
<input type="text" name="search"/>
<input type="submit" value="Search" />
</div> </form></div>
<div class="clear"></div>
<ul id="topmenu">
<li><a href="index.php">Search Results</a></li>
<li><a href="actors.php">View Actors</a></li>
<li class="active"><a href="directors.php">View Directors</a></li>
<li><a href="movies.php">View Movies</a></li>
<li><a href="add.php">Add Content</a></li>
<li><a href="comment.php">Add Comment</a></li>
</ul>
</div>
</div>

<div id="wrap">
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

	// query for information about the actor
	$query = "SELECT first, last, dob, dod, sex
		FROM Actor WHERE id = '$input'";

	$query2 = "SELECT first, last, dob, dod
		FROM Director WHERE id = '$input'";

	// query for movie information
	$movies = "SELECT title, role, mid, year
		FROM MovieActor MA, Movie M
		WHERE MA.aid = '$input' AND MA.mid = M.id ORDER BY year DESC";

	// query for director
	$director = "SELECT title, mid, year
		FROM MovieDirector MD, Movie M
		WHERE MD.did = '$input' AND MD.mid = M.id ORDER BY year DESC";

	$check = mysql_query($query, $db_connection);
	$checkdir = mysql_query($query2, $db_connection);
	$mov = mysql_query($movies, $db_connection);
	$dir = mysql_query($director, $db_connection);

	// show information about the actor
	//	echo "<b> Actor Information: </b>";
	if ($check || $checkdir){
		// check the number of rows
		$c = 0;
		if ($check)
			$c = mysql_num_rows($check);
		if ($c > 0)	
			$c = mysql_fetch_row($check);
		else 
			$c = mysql_fetch_row($checkdir);

		echo "<h2> Results for: " .$c[0]. " " .$c[1]. " </h2>";
		echo "<p>";
		echo "Name: <b>" .$c[0]. " " .$c[1]. "</b><br/>";
		echo "Gender: <b>" .$c[4]. "</b><br/>";
		echo "Date of Birth: <b>" .$c[2]. "</b><br/>";
		//if ($c[2] != "\N") // FIXME 
		echo "Date of Death: <b>" .$c[3]. "</b><br/>";
		//else { echo "Date of Death: N/A <br>"; }

	} else { echo "Invalid input. "; }

	echo "<p><p>";
	echo "<b><u> Filmography:</u> <br/></b>";
	echo "<b> Actor: </b><p>";

	if ($mov){
		while ($r = mysql_fetch_row($mov)){
			// for each element in that row
			echo "<a href = './movies.php?id=$r[2]'>";
			echo "" .$r[0]. " ";
			echo "</a>";
			echo "as \"" .$r[1]. "\"";
			echo " - " .$r[3]. ". <br/>";
		}
	} else { echo " Not an actor in a movie! "; }

	echo "</p><p><p>";
	echo "<b> Director: </b><p>";

	if ($dir){
		while ($d = mysql_fetch_row($dir)){
			// for each movie he's a director of
			echo "<a href = './movies.php?id=$d[1]'>";
			echo "" .$d[0]. "</a> - " .$d[2]. "";
			echo "<br/>";
		}
	} else { echo "Not a director of any movie!"; }


	// close database
	mysql_close($db_connection);
}
?>

<?php
// show results of actors that start with the letter
if($_GET[first] && !$_GET[last]){
	$let = $_GET[first];

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db_connection);


	$query = "SELECT first, last, id FROM Director
		WHERE first LIKE '$let%' ORDER BY first";

	echo "<h2>Directors starting with letter <u>".$let. "</u></h2>";
	$result = mysql_query($query, $db_connection);


	// filter by last name

	echo "<p>";
	echo "<b>Filter (by Last Name): </b><br>";
	$some = array(A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z);

	function last($curr, $letter)
	{
		echo " <a href=./directors.php?first=$curr&last=$letter>" .$letter. "</a> |";
	} 

	foreach ($some as $l){
		last($let, $l);
	} echo "</p>";

	if($result){
		echo "<h3>Results: </h3><p>";
		while($row = mysql_fetch_row($result)){

			echo "<a href = './directors.php?id=$row[2]'>";
			for($j=0; $j<2; $j++){
				echo "" .$row[$j]. " ";
			}
			echo "</a><br/>";
		}
		echo "</p>";
	}

	// close databse
	mysql_close($db_connection);
}elseif ($_GET[first] && $_GET[last]){
	$fir = $_GET[first];
	$lst = $_GET[last];	

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db_connection);

	$query = "SELECT first, last, id FROM Director
		WHERE first LIKE '$fir%' AND last LIKe '$lst%' ORDER BY first, last";

	echo "<h2>Directors starting with letter <u>".$fir. "</u> </br>
		with last beginning with <u>" .$lst. "</u></h2>";

	$result = mysql_query($query, $db_connection);

	// filter by last name

	echo "<p>";
	echo "<b>Change last name filter: </b><br>";	

	$some = array(A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z);
	function last($curr, $letter)
	{
		echo " <a href=./directors.php?first=$curr&last=$letter>" .$letter. "</a> |";
	} 

	foreach ($some as $l){
		last($fir, $l);
	} echo "</p>";


	if ($result){
		echo "<h3>Results: </h3><p>";
		while($row = mysql_fetch_row($result)){

			echo "<a href = './directors.php?id=$row[2]'>";
			for($j=0; $j<2; $j++){
				echo "" .$row[$j]. " ";
			}
			echo "</a><br/>";
		} echo "</p>";
	}
	// close databse
	mysql_close($db_connection);


}
?>

<?php
// PHP to display letters at the bottom
echo "<h3>View Directors (by First Name): </h3>";
$some = array(A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z);

foreach ($some as $l){
	letters($l);
}

function letters($letter)
{
	echo " <a href=./directors.php?first=$letter>" .$letter. "</a> ";
	if($letter != Z)
		echo "|";

}
?>

<p><p><p>




















</body>
</html>
