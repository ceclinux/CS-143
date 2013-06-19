
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
		echo "Date of Birth: <b>" .$c[2]. "</b><br/>";
		//if ($c[2] != "\N") // FIXME 
		echo "Date of Death: <b>" .$c[3]. "</b><br/>";
		//else { echo "Date of Death: N/A <br>"; }

	} else { echo "Invalid input. "; }

	echo "<p><p>";
	echo "<b><u><h4> Filmography:</u> </h4></b>";
	echo "<b> Actor: </b><p>";

	if ($mov && mysql_num_rows($mov)>0){
		while ($r = mysql_fetch_row($mov)){
			// for each element in that row
			echo "<a href = './movies.php?id=$r[2]'>";
			echo $r[0]."</a> ";
			echo "as \"" .$r[1]. "\"";
			echo " - " .$r[3]. ". <br/>";
		}
	} else { echo " Not an actor in a movie! "; }

	echo "</p><p><p>";
	echo "<b> Director: </b><p>";

	if ($dir && mysql_num_rows($dir)>0){
		while ($d = mysql_fetch_row($dir)){
			// for each movie he's a director of
			echo "<a href = './movies.php?id=$d[1]'>";
			echo "$d[0]</a> - $d[2] <br/>";
		}
	} else { echo "Not a director of any movie!"; }
	// see if they want to actor to movie
	echo "<form action=\"./add.php\" method=\"GET\">";
	echo "<input type=\"hidden\" name=\"type\" value =\"5\"/>";
	echo "<input type=\"hidden\" name=\"did\" value =\"$input\"/>";
	echo "<input type=\"submit\" value=\"Add to a movie!\"/>";
	echo "</form>";	

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
	foreach($some as $s)
		echo " <a href=./directors.php?first=$let&last=$s>$s</a> |";
	echo "</p>";

	if ($result && mysql_num_rows($result)>0){
		echo "<h3>Results: </h3><p>";
		
		// bordered box to display results (so it doesn't get ugly)
		echo "<div style=\"border:1px solid #8D6932;width:500px;height:500px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
	
		while($row = mysql_fetch_row($result)){
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";

			echo "<a href = './directors.php?id=$row[2]'>";
			for($j=0; $j<2; $j++){
				echo "" .$row[$j]. " ";
			}
			echo "</a><br/>";
		} 
		echo "</p>";
	} else { echo "<b>No actors found. </b>";}

	// close databse
	mysql_close($db_connection);
} elseif ($_GET[first] && $_GET[last]){
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
	foreach ($some as $s) 
		echo " <a href=./directors.php?first=$fir&last=$s>$s</a> |";
	echo "</p>";

 
	if ($result && mysql_num_rows($result) > 0){
		echo "<h3>Results: </h3><p>";
		
		// bordered box to display results (so it doesn't get ugly)
		echo "<div style=\"border:1px solid #8D6932;width:500px;height:500px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
		
		while($row = mysql_fetch_row($result)){
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";

			echo "<a href = './directors.php?id=$row[2]'>";
			for($j=0; $j<2; $j++){
				echo "" .$row[$j]. " ";
			}
			echo "</a><br/>";
		} 
		echo "</p></div>";
	} else { echo "<b>No directors found. </b>";}
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
