<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<link href="style.css" rel="stylesheet" type="text/css" />

<body>

<div id="contents">
<div id="left">

<?php
if($_GET["id"]){
	$movieid = $_GET["id"];

	 // establish connection
    $db = mysql_connect("localhost", "cs143", "");
     if(!$db) {
        $errmsg = mysql_error($db);
         print "Connection failed: $errmsg <br />";
         exit(1);
     }

	mysql_select_db("CS143", $db);
	
	$query = "SELECT title, id FROM Movie WHERE id = $movieid";

	$mov = mysql_query($query, $db);	

	if ($mov){
		$r = mysql_fetch_row($mov);
?>
<!-- form to review -->
<p>
<form method="post">
<p><h2>Review "<?php echo "<a href='./movies.php?id=$r[1]'><u>" .$r[0]. "</u></a>";?>"</h2><br/></p>
<p>Your Name: <input type="text" name="name" maxlength="20"/><br /></p>
<p>Rating:
<select name="rate">
<option value=5>5 - Excellent</option>
<option value=4>4 - Pretty good</option>
<option value=3>3 - Decent</option>
<option value=2>2 - Poor</option>
<option value=1>1 - Terrible</option>
</select></p>
<p>Your comment: <br/>
<textarea name="cmmt" cols="60" rows="8" maxlength="500"></textarea></p>
Max length: 500 characters
<p><input type="submit" value="Add Review!"/></p>
<br/>
</form>
</p>

<?php
	} else { echo "<b>Not a valid movie! </b>"; }
} else { echo "<b>Please search a movie you want to rate!</b>"; } 


if($_GET["id"] && $_POST["name"] && $_POST["cmmt"]){
	$tempname = $_POST["name"]; 				// raw input
	$tempcomment = $_POST["cmmt"]; 				// raw comment
	$id = $_GET["id"]; 							// id of movie
	$rate = $_POST["rate"]; 					// rating of movie
	$time = time(); 							// timestamp
	$mysqldate = date( 'Y-m-d H:i:s', $time );  // timestamp for MySQL
	$date = date("Y-m-d", $time); 				// was used for outputs

	//echo "<p>Time: " .$mysqldate. ".</p>" ;
	// check if name/ comments is just spaces
	if (str_replace(" ", "", $tempname) == ""){
		echo "Please enter a name! ";
	} elseif (str_replace(" ", "", $tempcomment) == ""){
		echo "Please enter a comment! ";
	} else {
	
    // establish connection
    $db = mysql_connect("localhost", "cs143", "");
    if(!$db) {
        $errmsg = mysql_error($db);
        print "Connection failed: $errmsg <br />";
        exit(1);
    }

    mysql_select_db("CS143", $db);

	$name = mysql_real_escape_string($tempname);
	$comment = mysql_real_escape_string($tempcomment);
	
	// query to insert review
	$query = "INSERT INTO Review VALUES
		('$name', '$mysqldate', $id, $rate, '$comment')";

	// successful insert!
	if(mysql_query($query, $db)){
		echo "<b>Successful add! Thank you $tempname! </b>";
	} else { echo "<b><p>Could not add to the database. </b></p> ";}

	// close database	
	mysql_close($db);

	}
}elseif($_GET["id"] && $_POST["name"] && !$_POST["cmmt"]){
	echo "Please enter in a comment! ";
}elseif($_GET["id"] && !$_POST["name"] && $_POST["cmmt"]){
	echo "Please enter a name! ";
}

?>
<?php

if($_POST["search"]){
	$raw = $_POST["search"];
	$firstc = substr($raw, 0, 1); 		// first character
	$shortflag = 0; 						// flag to see if short searches is okay
	$lengthflag = 0; // if the search is only one letter

	if ($firstc == "\\") {
		$shortflag = 1;
		$raw = substr($raw, 1);
	}

	$newraw = preg_replace('/\s+/', ' ', $raw); // replace multiple spaces with one
	$input = explode(" ", $newraw);

	// ensure each word is greater than size 1
	foreach ($input as $w){
		if (strlen($w) < 3)
			$lengthflag = 1;
	}

	if (str_replace(" ", "", $newraw) == ""){
		echo "<b><p>Please enter a valid input!</p></b>";
	} elseif ($lengthflag == 1 && $shortflag == 0) {
		// if short flag isn't on and length is only 1 character, error
		echo "<b><p>Please enter at least 3 characters per word!</b><br/>";
		echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
		echo "To overwrite this, enter '\\' before your search</p>";
	} else {
	
		 // establish connection
	    $db = mysql_connect("localhost", "cs143", "");
	     if(!$db) {
	        $errmsg = mysql_error($db);
	         print "Connection failed: $errmsg <br />";
	         exit(1);
	     }
	
		mysql_select_db("CS143", $db);
		
		// query to select the movie titles
		$mquery= "SELECT title, id FROM Movie WHERE title LIKE '%$input[0]%'";
	
		foreach($input as $t){
			$mquery .= " OR title LIKE '%$t%'";
		}
		$mquery .= " ORDER BY title";
	
		// query 
		$result = mysql_query($mquery, $db);
	
		// if there's a result, display the movies and links
		if($result && mysql_num_rows($result)>0){
			echo "<p><h3> Select a movie to review! </h3></p>";
			
			// bordered box to display results (so it doesn't get ugly)
			echo "<div style=\"border:1px solid #8D6932;width:500px;height:500px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
			
			while($r = mysql_fetch_row($result)){
				echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
				echo "<a href = './comment.php?id=$r[1]'>";
				echo "$r[0]</a><br/>
				";
			}
			echo "</p></div>";
		} else { echo "<b><p>No movie with \"$raw\" </b></p>"; }
		
		// close database
		mysql_close($db);
	}
}
?>

<!-- form for searching a movie -->
<p>
Search a movie: <br/>
<form method="POST">
<input type="text" name="search" />
<input type="submit" value="Search Movies" />
</form>
</p>

