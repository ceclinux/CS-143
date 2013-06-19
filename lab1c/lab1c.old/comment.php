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
<li><a href="movies.php">View Movies</a></li>
<li><a href="add.php">Add Content</a></li>
<li class="active"><a href="comment.php">Add Comment</a></li>
</ul>
</div>
</div>

<div id="wrap">
<div id="contents">
<div id="left">


<?php
if($_GET["id"]){
	$movieid = $_GET["id"];

	 // establish connection
    $db_connection = mysql_connect("localhost", "cs143", "");
     if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
         print "Connection failed: $errmsg <br />";
         exit(1);
     }

	mysql_select_db("CS143", $db_connection);
	
	$query = "SELECT title, id FROM Movie WHERE id = $movieid";

	$mov = mysql_query($query, $db_connection);	

	if ($mov){
		$r = mysql_fetch_row($mov);
?>

<p>
<form method="post">
<p><h2>Review "<?php echo "<a href='./movies.php?id=$r[1]'><u>" .$r[0]. "</u></a>";?>"</h2><br/></p>
<p>Your Name: <input type="text" name="name" /><br /></p>
<p>Rating:
<select name="rate">
<option value=5>5 - Excellent</option>
<option value=4>4 - Pretty good</option>
<option value=3>3 - Decent</option>
<option value=2>2 - Poor</option>
<option value=1>1 - Terrible</option>
</select></p>
<p>Your comment: <br/>
<textarea name="cmmt" cols="60" rows="8"></textarea></p>
<p><input type="submit" value="Add Review!"/></p>
<br/>
</form>
</p>

<?php

		
	} else { echo "Not a valid movie! "; }
} else { echo "Please search a movie!"; } 


if($_GET["id"] && $_POST["name"] && $_POST["cmmt"]){
	$name = $_POST["name"];
	$comment = $_POST["cmmt"];
	$id = $_GET["id"];
	$rate = $_POST["rate"];
	$time = time();
	$mysqldate = date( 'Y-m-d H:i:s', $time );
	$date = date("Y-m-d", $time);

	//echo "<p>Time: " .$mysqldate. ".</p>" ;
	// check if name/ comments is just spaces
	if (str_replace(" ", "", $name) == ""){
		echo "Please enter a name! ";
	} elseif (str_replace(" ", "", $comment) == ""){
		echo "Please enter a comment! ";
	} else {
	
    // establish connection
    $db_connection = mysql_connect("localhost", "cs143", "");
    if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
        print "Connection failed: $errmsg <br />";
        exit(1);
    }

    mysql_select_db("CS143", $db_connection);

	// query to insert review
	$query = "INSERT INTO Review VALUES
		('$name', '$mysqldate', $id, $rate, '$comment')";

	// successful insert!
	if(mysql_query($query, $db_connection)){
		echo "Successful add! Thank you " .$name. "";
	} else { echo "Something went wrong. ";}

	// close database	
	mysql_close($db_connection);

	}
}elseif($_GET["id"] && $_POST["name"] && !$_POST["cmmt"]){
	echo "Please enter in a comment! ";
}elseif($_GET["id"] && !$_POST["name"] && $_POST["cmmt"]){
	echo "Please enter a name! ";
}

?>
<p>
Search a movie: <br/>
<form method="POST">
<input type="text" name="search" />
<input type="submit" value="Search Movies" />
</form>
</p>

<?php

if($_POST["search"]){
	$raw = $_POST["search"];
	$input = explode(" ", $raw);

	 // establish connection
    $db_connection = mysql_connect("localhost", "cs143", "");
     if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
         print "Connection failed: $errmsg <br />";
         exit(1);
     }

	mysql_select_db("CS143", $db_connection);
	
	// query to select the movie titles
	$mquery= "SELECT title, id FROM Movie WHERE title LIKE '%$input[0]%'";

	foreach($input as $t){
		$mquery .= " OR title LIKE '%$t%'";
	}
	$mquery .= " ORDER BY title";

	// query 
	$result = mysql_query($mquery, $db_connection);

	// if there's a result, display the movies and links
	if($result){
		while($r = mysql_fetch_row($result)){
			echo "<a href = './comment.php?id=$r[1]'>";
			echo "" .$r[0]. "</a><br/>";
		}
	} else { echo "No movie with \"" .$input. "\""; }

}
?>
