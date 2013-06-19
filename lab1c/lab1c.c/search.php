<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css" />
<body>
<div id="contents">
<div id="left">
<?php
// get the input
if($_POST["search"]){
	$rawinput = $_POST["search"]; // raw input
	$newinput = preg_replace('/\s+/', ' ', $rawinput); // replace multiple spaces with one
	$words = explode(" ", $newinput); // separate words with explode

	// header
	echo "<h2>Search results for $rawinput: </h2>";

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}
	mysql_select_db("CS143", $db);

	// for each database, we'll do something
	$dbname = array(Actor, Director, Movie);
	$queries = array();

	// for each word, call mysql_real_escape_string
	// 		and append it to the input array
	$input = array();
	foreach ($words as $w){
		array_push($input, mysql_real_escape_string($w));
	}
	
	// actor, director, movie queries
	$queries[0] = "SELECT DISTINCT first, last, dob, id FROM Actor WHERE first LIKE '%$input[0]%'";
	$queries[1] = "SELECT DISTINCT first, last, dob, id FROM Director WHERE first LIKE '%$input[0]%'";
	$queries[2] = "SELECT DISTINCT title, id, year FROM Movie WHERE title LIKE '%$input[0]%'";
	
	// for each word, append to the query
	foreach($input as $v){
		$queries[0] .= " OR first LIKE '%$v%' OR last LIKE '%$v%'";
		$queries[1] .= " OR first LIKE '%$v%' OR last LIKE '%$v%'";
		$queries[2] .= " OR title LIKE '%$v%'";
	}
	
	// finally, order by first and last, or title
	$queries[0] .= " ORDER BY first, last";
	$queries[1] .= " ORDER BY first, last";
	$queries[2] .= " ORDER BY title";

	$a = 0;
	foreach($dbname as $val){

		echo "<h4> $val:</h4>";

		$result = mysql_query($queries[$a], $db);

		$j = 0;
		if ($result && mysql_num_rows($result)>0){
			// read a row
			echo "<p>";
			while ($row = mysql_fetch_row($result)){
				// for each element in that row
				if ($val == Movie)
					echo "<a href = './movies.php?id=$row[1]'>";
				elseif ($val == Actor)
					echo "<a href = './actors.php?id=$row[3]'>";
				else echo "<a href = './directors.php?id=$row[3]'>";
				$y = 2; // display first, last for people
				if ($val == Movie) 
					$y = 1; // only display title for movies
				for($j=0; $j<$y; $j++){
					echo "$row[$j]";
					if ($j<$y-1) echo " "; // just add space after first name
				}
				echo "</a>";

				// if it's a actor or director, display DOB
				// if it's a movie, display production year
				if($val != Movie)
					echo " Born: $row[2] ";
				else
					echo " - $row[2]";
				echo "<br/>";
			} 
			echo "</p>";
		} else { echo "<p><b> Nothing found with \"$rawinput\"</b></p>"; }

		// next one
		$a++;		
	}

	// close database
	mysql_close($db);
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

