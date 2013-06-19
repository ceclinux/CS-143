
<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<link href="style.css" rel="stylesheet" type="text/css" />

<body>

<div id="contents">
<div id="left">

<?php
// get the input
if($_GET["id"]){
	$input = $_GET["id"]; // director ID

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db);

	// query for information about the director
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

	$check = mysql_query($query, $db);
	$checkdir = mysql_query($query2, $db);
	$mov = mysql_query($movies, $db);
	$dir = mysql_query($director, $db);

	// really messy checking >_<
	if (/*($check && mysql_num_rows($check) <= 0) &&*/
		($checkdir && mysql_num_rows($checkdir) <= 0)){
		
		// if the person is neither a director or actor 
		echo "<b>Not a valid director id.</b>";
	} else {
		// show information about the director
		if (($check && mysql_num_rows($check) > 0) || 
			($checkdir && (mysql_num_rows($checkdir) > 0))){
		
			// fetch the director
			$c = mysql_fetch_row($checkdir);
	
			echo "<h2> Results for: $c[0] $c[1]</h2><p>";
			echo "Name: <b>" .$c[0]. " " .$c[1]. "</b><br/>";
			$dob = date("F d, Y", strtotime($c[2])); // format DOB
			echo "Date of Birth: <b>$dob</b><br/>";
			if ($c[3]) // format the death date
				$dod = date("F d, Y", strtotime($c[3])); 
			echo "Date of Death: <b>$dod</b><br/>"; 
	
		} else { echo "<b>Not a valid director id. </b>"; }
	
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
		
		// see if they want to actor to movie .. ugly code
		echo "<form action=\"./add.php\" method=\"GET\">";
		echo "<input type=\"hidden\" name=\"type\" value =\"5\"/>";
		echo "<input type=\"hidden\" name=\"did\" value =\"$input\"/>";
		echo "<input type=\"submit\" value=\"Add to a movie!\"/>";
		echo "</form>";	
	}

	// close database
	mysql_close($db);
}
?>

<!-- BROWSING MODE -->
<?php
// show results of actors that start with the letter
if($_GET[first] && !$_GET[last]){
	$let = $_GET[first];

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db);


	$query = "SELECT first, last, id FROM Director
		WHERE first LIKE '$let%' ORDER BY first";

	echo "<h2>Directors starting with letter <u>".$let. "</u></h2>";
	$result = mysql_query($query, $db);


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
		echo "<div style=\"border:1px solid #8D6932;width:500px;height:60%;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
	
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

	// close database
	mysql_close($db);
	
} elseif ($_GET[first] && $_GET[last]){
	$fir = $_GET[first];	// first name starts with
	$lst = $_GET[last];		// last name starts with

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db);

	// get the directors
	$query = "SELECT first, last, id FROM Director
		WHERE first LIKE '$fir%' AND last LIKE '$lst%' ORDER BY first, last";

	echo "<h2>Directors starting with letter <u>".$fir. "</u> </br>";
	echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "with last beginning with <u>" .$lst. "</u></h2>";

	$result = mysql_query($query, $db);

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
		echo "<div style=\"border:1px solid #8D6932;width:500px;height:60%;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
		
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
	mysql_close($db);

}
?>

<?php
// PHP to display letters at the bottom
echo "<h3>View Directors (by First Name): </h3>";
$some = array(A, B, C, D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U, V, W, X, Y, Z);

foreach ($some as $l){
	echo " <a href=./directors.php?first=$l>$l</a> ";
	if($letter != Z)
		echo "|";
}
?>

</body>
</html>
