<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css" />
<body>
<div id="contents">
<div id="left">
<?php
// get the input
if($_POST["search"]){
	$rawinput = $_POST["search"];
	$input = explode(" ", $rawinput);

	echo "<h2>Search results for " .$rawinput. ": </h2>";

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db_connection);

	$db = array(Actor, Director, Movie);
	$queries = array();

	// actor, director, movie queries
	$queries[0] = "SELECT DISTINCT first, last, dob, id FROM Actor WHERE first LIKE '%$input[0]%'";
	$queries[1] = "SELECT DISTINCT first, last, dob, id FROM Director WHERE first LIKE '%$input[0]%'";
	$queries[2] = "SELECT DISTINCT title, id FROM Movie WHERE title LIKE '%$input[0]%'";

	foreach($input as $v){
		$queries[0] .= " OR first LIKE '%$v%' OR last LIKE '%$v%'";
		$queries[1] .= " OR first LIKE '%$v%' OR last LIKE '%$v%'";
		$queries[2] .= " OR title LIKE '%$v%'";
	}

	$queries[0] .= " ORDER BY first, last";
	$queries[1] .= " ORDER BY first, last";
	$queries[2] .= " ORDER BY title";

	$a = 0;
	foreach($db as $val){

		echo "<h4> $val:</h4>";

		$result = mysql_query($queries[$a], $db_connection);

		$j = 0;
		if ($result){
			// read a row
			while ($row = mysql_fetch_row($result)){
				echo "<p>";
				// for each element in that row
				if ($val == Movie)
					echo "<a href = './movies.php?id=$row[1]'>";
				elseif ($val == Actor)
					echo "<a href = './actors.php?id=$row[3]'>";
				else echo "<a href = './directors.php?id=$row[3]'>";
				$y = 2;
				if ($val == Movie){$y = 1;}
				for($j=0; $j<$y; $j++){
					echo "" .$row[$j]. " ";
				}
				echo "</a>";

				if($val != Movie)
					echo " Born: " .$row[2]. "";
				echo "</p>";
			}
		}

		// next one
		$a++;		
	}

	// close database
	mysql_close($db_connection);
}
else {
	echo "<h2> Search something! </h2>";
}
?>

<p>
Search another: <br/>
<form method="POST">
<input type="text" name="search" placeholder="Search something!"/><input type="submit" value="Search" />
</form>
</p>



</div>



</body>
</html>

