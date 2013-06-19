<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="style.css" rel="stylesheet" type="text/css" />

</head>
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
<li class="active"><a href="index.php">Search Results</a></li>
<li><a href="actors.php">View Actors</a></li>
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

	$db = array(Actor, Director, Movie);

	mysql_select_db("CS143", $db_connection);

	$queries = array();
	$x = 0;	

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

	/*
	   foreach($db as $val){
	// pOSSIBLY FIXME				 
	foreach($input as $v){

	// search actors
	if ($val == Actor || $val == Director)
	$queries[$x] = "SELECT first, last, dob, id FROM $val WHERE first LIKE '%$v%' OR
	last LIKE '%$v%' ORDER BY first";
	else
	$queries[$x] = "SELECT title, id FROM $val WHERE title LIKE '%$v%'";

	}
	$x++;
	}
	 */
	$a = 0;
	foreach($db as $val){
		// select database
//		mysql_select_db("CS143", $db_connection);

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
<input type="text" name="search" /><input type="submit" value="Search" />
</form>
</p>



</div>



</body>
</html>

