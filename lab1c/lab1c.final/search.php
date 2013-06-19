<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css" />
<body>
<div id="contents">
<div id="left">
<?php
// get the input
if($_POST["search"] && ($_POST["clicked"]=="yes")){
	$rawinput = $_POST["search"]; 			// raw input
	$firstc = substr($rawinput, 0, 1); 		// first character
	$shortflag = 0; 						// flag to see if short searches is okay
	$lengthflag = 0; 						// if the search is only two letters
	
	if ($firstc == "\\") {
		$shortflag = 1;
		$rawinput = substr($rawinput, 1);
	}
	
	$newinput = preg_replace('/\s+/', ' ', $rawinput); // replace multiple spaces with one
	$words = explode(" ", $newinput); 		// separate words with explode
	
	// ensure each word is greater than size 2
	foreach ($words as $w){
		if (strlen($w) < 3)
			$lengthflag = 1;
	}
	

	if (str_replace(" ", "", $newinput) == ""){	
		// if search is just spaces, error
		echo "<b><p>Please enter a valid input!</p></b>";
	} elseif ($lengthflag == 1 && $shortflag == 0) {
		// if short flag isn't on and length is only 1 character, error
		echo "<b><p>Please enter at least 3 characters per word!</b><br/>";
		echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
		echo "To overwrite this, enter '\\' before your search</p>";
	} else {
	
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
					// determine which url to link to
					if ($val == Movie)
						echo "<a href = './movies.php?id=$row[1]'>";
					elseif ($val == Actor)
						echo "<a href = './actors.php?id=$row[3]'>";
					else echo "<a href = './directors.php?id=$row[3]'>";
					
					// which to output
					if ($val != Movie){
						$dob = date("F d, Y", strtotime($row[2])); // format DOB
						echo "$row[0] $row[1]</a>  Born: $dob";
					}
					else {
						echo "$row[0]</a> - $row[2]";
					}
					echo "<br/>";
				} 
				echo "</p>";
			} else { echo "<p><b> No $val found with \"$rawinput\"</b></p>"; }
	
			// next one
			$a++;		
		}
		
		// close database
		mysql_close($db);
	}


} else {
	echo "<h2> Search something! </h2>";
	
	if ($_POST["clicked"]=="yes")
		echo "<br/><b> Error: enter something! <b/><br/>";
}
?>

<p>
Search another: <br/>
<form method="POST">
<input type ="hidden" name="clicked" value="yes" />
<input type="text" name="search" placeholder="Search something!"/><input type="submit" value="Search" />
</form>
</p>

</div>

</body>
</html>

