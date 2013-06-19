<html>
<head><title>CS143 Project 1C by Jonathan Nguy</title></head>
<link href="style.css" rel="stylesheet" type="text/css" />

<body>

<div id="contents">
<div id="left">

<!-- DROPDOWN TO SELECT FORM -->
<form method="GET">
<center>
<select name="type">
<option value="0">PLEASE SELECT ONE</option>
<option value="1" <?php if($_GET["type"] == 1) echo "selected=\"selected\""; ?>>Add Actor</option>
<option value="2" <?php if($_GET["type"] == 2) echo "selected=\"selected\""; ?>>Add Director</option>
<option value="4" <?php if($_GET["type"] == 4) echo "selected=\"selected\""; ?>>Add Movie </option>
<option value="5" <?php if($_GET["type"] == 5) echo "selected=\"selected\""; ?>>Add (Director or Actor)/Movie relation</option>
</select>
<input type="submit" value="Change Form" />
</center>
</form>
</font>


<!-- ACTOR and DIRECTOR FORM -->
<?php
if($_GET["type"]){
	$type = $_GET["type"];

	// form for adding actor or director
	if ($type == "1" || $type == "2"){ ?>
<p>
<form method="post">
<?php 
	if($_GET["type"] == 1) 
		echo "<h3>Add a new actor: </h3>"; 
	else 
		echo "<h3>Add a new director: </h3>"; ?>
<p>First: <input type="text" name="first" maxlength="20"/><br/></p>
<p>Last: <input type="text" name="last" maxlength="20" /><br/></p>
<p>Gender: <input type="radio" name="sex" value="Male" checked/> Male 
<input type="radio" name="sex" value="Female"/> Female <br/></p>
<p>Date of Birth: 
<select name="dobm"><option value="0"></option>
<?php 
 	$month = array( '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
 					'05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', 
 					'09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');

	// for each month, add to the option list
	foreach ($month as $k => $m) echo "<option value=\"$k\">$m</option>"; 
 
 ?>
 </select>
 <select name="dobd"><option value="0"></option>
 <?php for ($x=01;$x<32;$x++) echo "<option value=\"$x\">$x</option>"; ?></select>
 <select name="doby"><option value="0"></option>
 <?php for ($x=2012;$x>1900;$x--) echo "<option value=\"$x\">$x</option>"; ?></select>
 (Month - Day - Year) 
</p>
<p>Date of Death (if Applicable): 
 <select name="dodm"><option value="0"></option>
 <?php 	// for each month, add to the option list
	foreach ($month as $k => $m) echo "<option value=\"$k\">$m</option>";	?></select>
 <select name="dodd"><option value="0"></option>
 <?php for ($x=01;$x<32;$x++) echo "<option value=\"$x\">$x</option>"; ?></select>
 <select name="dody"><option value="0"></option>
 <?php for ($x=2012;$x>1900;$x--) echo "<option value=\"$x\">$x</option>"; ?></select>
 (Month - Day - Year) 
</p>

<p><input type="submit" value="Add Person" name="clicked"/></p>
</form>
</p>

<?php } // close adding director/ actor form if statement?>
<!-- END ACTOR and DIRECTOR FORM -->

<!--#############################################################-->

<!-- ___________ MOVIE FORM _____________ -->
<?php if ($type == "4"){ // adding a movie form ?>
<p>
<form method="post">
<h3>Add a new movie: </h3>
<input type="hidden" name="clicked" maxlength="100"/>
<p>Title: <input type="text" name="title" <?php if(!$_GET["did"]) echo "disabled=\"disabled\""; ?>/><br/></p>
<p>Company: <input type="text" maxlength="50" name="company" <?php if(!$_GET["did"]) echo "disabled=\"disabled\""; ?>/><br/></p>
<p>Year: <input type="text" name="year" <?php if(!$_GET["did"]) echo "disabled=\"disabled\""; ?>/> (between 1900 and 2025)<br/></p>
<p>Director: 

<?php
	// If there's a director selected, display his name 
	if ($_GET["did"] > 1){
		$did = $_GET["did"]; // director ID
		
		// establish connection
		 $db = mysql_connect("localhost", "cs143", "");
			if(!$db) {
			 $errmsg = mysql_error($db);
				 print "Connection failed: $errmsg <br />";
				 exit(1);
			}
		mysql_select_db("CS143", $db);
	
		// get the director
		$direct = "SELECT first, last, id FROM Director WHERE id = $did";
	
		// get the results
		$result = mysql_query($direct, $db);
			
		if($result && mysql_num_rows($result) > 0){
			$r = mysql_fetch_row($result);
			echo "<a href='./directors.php?id=$did'><b>$r[0] $r[1]</a> </b> "; // show the directo
			echo "(<a href='./add.php?type=4'><i>clear</i></a>)"; // let the person search again
		}

	} elseif ($_GET["did"] == 1) { 
		// if they want to add a director instead
		echo " <a href='./add.php?type=4'><i>change your mind?</i></a>";
	} else {
// if there's no director selected, let them search
?>
<form method="POST">
<input type="text" name="director" placeholder="Search a name!"/>
<input type="hidden" name="clicked" value="dirsearch"/>
<input type="submit" value="Search!" />
(click <a href="./add.php?type=4&did=1"><i>here</i></a> to add without a director)
</p>
</form>
<?php } 

if ($type == "4" && ($_POST["director"]) && ($_POST["clicked"] == "dirsearch")){
	$raw = $_POST["director"]; 			// search query
	$firstc = substr($raw, 0, 1); 		// first character
	$shortflag = 0; 					// flag to see if short searches is okay
	$lengthflag = 0; 					// if the search is only two letters
	
	if ($firstc == "\\") {
		$shortflag = 1;
		$raw = substr($raw, 1);
	}
	
	$newinput = preg_replace('/\s+/', ' ', $raw); 	// replace multiple spaces with one
	$input = explode(" ", $newinput); 				// separate words with explode
	
	// ensure each word is greater than size 2
	foreach ($input as $w){
		if (strlen($w) < 3)
			$lengthflag = 1;
	}
	
	// query to search directors
	$dquery = "SELECT DISTINCT first, last, id FROM Director WHERE first LIKE '%$input[0]%'";
	foreach($input as $d){
		$dquery .= " OR first LIKE '%$d%' OR last LIKE '%$d%'";
	}
	$dquery .= " ORDER BY first, last";
	
	// if search is just spaces, exit
	if (str_replace(" ", "", $newinput) == ""){
		echo "<b><p>Please enter a valid input!</p></b>";
	} elseif ($lengthflag == 1 && $shortflag == 0) {
		// if short flag isn't on and length is only 1 character, error
		echo "<b><p>Please enter at least 2 characters per word!</p></b>";
		echo "</br> To overwrite this, enter '\\' before your search";
	} else {;
	
		// establish connection
		 $db = mysql_connect("localhost", "cs143", "");
		  if(!$db) {
			 $errmsg = mysql_error($db);
				print "Connection failed: $errmsg <br />";
				exit(1);
		}
		mysql_select_db("CS143", $db);
		
		// query
		$result = mysql_query($dquery, $db);
	
		// if there's a result then output the director names
		if ($result && mysql_num_rows($result) > 0){
			echo "<p><b>Your search results:</b> <br/>";
					
			// bordered box to display results (so it doesn't get ugly)
			echo "<div style=\"border:1px solid #8D6932;width:250px;height:300px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
			
			while ($r = mysql_fetch_row($result)){
				echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
				echo "<a href='./add.php?type=4&did=$r[2]'>";
				echo "$r[0] $r[1] </a><br/>";
			} echo "</p></div>";
		} else { echo "No director found, please search again!" ; }
		
		mysql_close($db); // close database
	}

}

?>

<p>MPAA Rating: 
<select name="MPAA">
<option value="G">G</option>
<option value="NC-17">NC-17</option>
<option value="PG">PG</option>
<option value="PG-13">PG-13</option>
<option value="R">R</option>
</select></p><br/>
Genre (check all that apply): <br/></br/>
<?php
$genres = array("Action", "Adult", "Adventure", "Animation", "Comedy", "Crime",
		"Documentary", "Drama", "Family", "Fantasy", "Horror", "Musical", "Mystery",
		"Romance", "Sci-Fi", "Short", "Thriller", "War", "Western");

			
	// bordered box to display results (so it doesn't get ugly)
	echo "<div style=\"border:1px solid #8D6932; width:150px;height:200px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left;\" ><p>";
	foreach ($genres as $gen){
		echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
		echo "<input type=\"checkbox\" name=\"genre[]\" value=\"$gen\"/> $gen <br/> ";
	} 
	echo "</p></div>";
?>
<p><input type="submit" value="Add Movie" <?php if(!$_GET["did"]) echo "disabled=\"disabled\"";?>/>
<?php if($type == "4" && !$_GET["did"]) {?>
(select a director first, or click <a href="./add.php?type=4&did=1"><i>here</i></a>)
<?php } ?>
</form>
</p>


<?php
if ($type == "4" && $_GET["did"] && $_POST["title"] && $_POST["company"]
&& $_POST["year"] && $_POST["MPAA"] && is_numeric($_POST["year"])){
	
	$did = $_GET["did"];
	$title = $_POST["title"];
	$company = $_POST["company"];
	$year = $_POST["year"];
	$mpaa = $_POST["MPAA"];
	$temp = array("Action", "Adult", "Adventure", "Animation", "Comedy", "Crime",
		"Documentary", "Drama", "Family", "Fantasy", "Horror", "Musical", "Mystery",
		"Romance", "Sci-Fi", "Short", "Thriller", "War", "Western");
	$genres = array();

	// FIXME 
	// check to see if year is a valid year
	if($year > 2025 || $year <1900){
		echo "Please enter a year between 1900 and 2050. ";
		exit(0);
	}

	// go through all the checkboxes
	for($x=0;$x<19;$x++){
		if(isset($_POST["genre"][$x]))
			array_push($genres, $_POST["genre"][$x]);
	}
	
	// create db connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	} 
	mysql_select_db("CS143", $db);

	// get the latest mid
	$midquery = "SELECT id FROM MaxMovieID";
	$midsearch = mysql_query($midquery, $db);
	$midfinished = mysql_fetch_row($midsearch);
	$mid = $midfinished[0];
	
	//query to add into database
	$addm = "INSERT INTO Movie VALUES($mid, '$title', $year, '$mpaa', '$company')";
	$adddir = "INSERT INTO MovieDirector VALUES ($mid, $did)";
	$update = "UPDATE MaxMovieID SET id=id+1";

	// check to see if it's a sucessful add
	if(mysql_query($addm, $db)){
		mysql_query($adddir, $db);
		echo "<p>Successfully added \"$title\" into the database! Thank you! </p>";
		echo "View your movie <a href='./movies.php?id=$mid'>here</a></br> ";

		// for each 
		foreach($genres as $g){
			mysql_query("INSERT INTO MovieGenre VALUES ($mid, '$g')", $db);
		}
		// update max id counter
		mysql_query($update, $db);

	} else { echo "Error in inserting movie, please try again! "; }

	mysql_close($db);
}
?>

<!-- ___________________ RELATIONSHIP FORM ___________________ -->

<?php
// display movie search results for relationship
}

if ($type == "5" && $_GET["mid"]){
	$mid = $_GET["mid"];

	// create db connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}					
	mysql_select_db("CS143", $db);

	// get movie title
	$qTitle = "SELECT title FROM Movie WHERE id = $mid";
	$title = mysql_query($qTitle, $db);

	if ($title && mysql_num_rows($title) > 0){
		$r = mysql_fetch_row($title);
		echo "<h4>Movie : $r[0]</h4>";
	} else { echo "No movie with that ID"; }

	mysql_close($db);	
}?>


<?php
// display actor search results for relationship
if ($type == "5" && $_GET["aid"]){
	 $aid = $_GET["aid"];
 
	 // create db connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		 exit(1);
	}
	mysql_select_db("CS143", $db);
 
	// get actor title
	$qName = "SELECT first, last FROM Actor WHERE id = $aid";
	$title = mysql_query($qName, $db);

	if ($title && mysql_num_rows($title) > 0){
		$r = mysql_fetch_row($title);
		echo "<h4>Actor : $r[0] $r[1]</h4>";
	} else { echo "No actors with that ID"; }
 
	 mysql_close($db);
 } 
?>

<?php
// display director search results for relationship
if ($type == "5" && $_GET["did"]){
	 $did = $_GET["did"];
 
	 // create db connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		print "Connection failed: $errmsg <br />";
		 exit(1);
	}
	mysql_select_db("CS143", $db);
 
	// get director title
	$qName = "SELECT first, last FROM Director WHERE id = $did";
	$title = mysql_query($qName, $db);

	if ($title && mysql_num_rows($title) > 0){
		$r = mysql_fetch_row($title);
		echo "<h4>Director : $r[0] $r[1]</h4>";
	} else { echo "No directors with that ID"; }
 
	 mysql_close($db);
 } 
?>

<!-- ________ IF THERE'S A MOVIE AND ACTOR _________-->
<?php if ($type == "5" && $_GET["mid"] && $_GET["aid"]){ ?>
<form method = "POST">
<h4>Role : <input type="text" name="role" maxlength="50"/></h4>
<p>Do you wish to add a relationship between these two?
<input type="hidden" name="select" value="addam"/>
<input type="hidden" name="disable" value="yes"/>
<input type="submit" value="Add relation!" 
<?php // if you've clicked it once, disable the button.
	if($_POST["disable"]=="yes" && $_POST["role"]) echo "disabled=\"disabled\""; ?>/></p>
</form>
<?php } // end bracket for if it's showing a pair ?>
<?php
// if user decides to add actor/relation to the movies
if ($type == "5" && $_POST["select"] == "addam" && $_POST["role"]){
	$mid = $_GET["mid"]; // movie id
	$aid = $_GET["aid"]; // actor id
	$temprole = $_POST["role"]; // role

	 // establish connection
	$db = mysql_connect("localhost", "cs143", "");
	 if(!$db) {
		$errmsg = mysql_error($db);
		 print "Connection failed: $errmsg <br />";
		 exit(1);
	 }
	 
	mysql_select_db("CS143", $db);
	
	if (str_replace(" ", "", $temprole) == ""){
		echo "Please enter a valid role!";
	} else {
	
		// add escape strings
		$role = mysql_real_escape_string($temprole);
	
		// add query	
		$relation = "INSERT IGNORE INTO MovieActor VALUES ($mid, $aid, '$role')";
	
		// if/not sucessfully added into database
		if (mysql_query($relation, $db)){
			echo "<p><b><u>Sucessfully added actor to movie! </b></u>";
			echo "<br/>View your new relation <a href='./movies.php?id=$mid'>here</a></p>";
		} else { echo "<b>Couldn't add actor to movie!</b>"; }
	}

	//close connection
	mysql_close($db);
} elseif ($type == "5" && $_POST["select"] == "addam" && !$_POST["role"]){
	echo "<b>Please enter a role!</b>";
}
?>

<!-- ________ IF THERE'S A MOVIE AND DIRECTOR _________-->
<?php if ($type == "5" && $_GET["mid"] && $_GET["did"]){ ?>
<form method = "POST">
<input type="hidden" name="select" value="adddm"/>
<input type="hidden" name="disable" value="yes"/>
<input type="submit" value="Add relation!" 
<?php // if you've clicked it once, disable the button.
	if($_POST["disable"]=="yes") echo "disabled=\"disabled\""; ?>/></p>
</form>
<?php } // end bracket for if it's showing a pair ?>
<?php
// if user decides to add director/relation to the movies
if ($type == "5" && $_POST["select"] == "adddm"){
	$mid = $_GET["mid"]; // movie id
	$did = $_GET["did"]; // director id
	
	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	 if(!$db) {
		$errmsg = mysql_error($db);
		 print "Connection failed: $errmsg <br />";
		 exit(1);
	 }
	mysql_select_db("CS143", $db);
	
	// add query	
	$relation = "INSERT IGNORE INTO MovieDirector VALUES ($mid, $did)";
	
	// if/not sucessfully added into database
	if (mysql_query($relation, $db)){
		echo "<p><b><u>Sucessfully added director to movie! </b></u>";
		echo "<br/>View your new relation <a href='./movies.php?id=$mid'>here</a></p>";
	} else { echo "<b><p>Coudn't add director to movie!</b></p>"; }

	//close connection
	mysql_close($db);
	
}?>

<!-- IF THE PERSON CLICKS SEARCH MOVIE -->
<?php
// searching a movie for movie/actor relation
if($_POST["smovie"] && ($_POST["but"] == "mov")){
	$raw = $_POST["smovie"]; // get movie search
	$newinput = preg_replace('/\s+/', ' ', $raw); // replace multiple spaces with one
	$words = explode(" ", $newinput); // separate words with explode
	
	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	if(!$db) {
		$errmsg = mysql_error($db);
		 print "Connection failed: $errmsg <br />";
		 exit(1);
	}
	mysql_select_db("CS143", $db);
	
	// for each word, call mysql_real_escape_string
	// 		and append it to the input array
	$input = array();
	foreach ($words as $w){
		array_push($input, mysql_real_escape_string($w));
	}
	
	// query to select the movie titles
	$mquery= "SELECT title, id FROM Movie WHERE title LIKE '%$input[0]%'";

	foreach($input as $t){
		$mquery .= " OR title LIKE '%$t%'";
	}
	$mquery .= " ORDER BY title";

	// query 
	$result = mysql_query($mquery, $db);

	// if there's a result, display the movies and links
	if($result && mysql_num_rows($result) > 0){
		echo "<br/><b><p>Movies: </p></b><p>";
				
		// bordered box to display results (so it doesn't get ugly)
		echo "<div style=\"border:1px dashed #8D6932;width:450px;height:300px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
				
		while($r = mysql_fetch_row($result)){
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
			if ($_GET["aid"]){
				$aid = $_GET["aid"];
				echo "<a href = './add.php?type=5&aid=$aid&mid=$r[1]'>";
			} elseif ($_GET["did"]){
				$did = $_GET["d"];
				echo "<a href = './add.php?type=5&aid=$did&mid=$r[1]'>";
			} else 
				echo "<a href = './add.php?type=5&mid=$r[1]'>";
			echo "" .$r[0]. "</a><br/>";
		}
		echo "</p></div>";
	} else { echo "<br/><b>No movie found with search \"$raw\"</b>"; }

		// close database	  
		mysql_close($db);
}
?>

<!-- IF THE PERSON CLICKS SEARCH ACTOR -->
<?php
if($_POST["sactor"] && ($_POST["but"] == "act")){
	$raw = $_POST["sactor"]; // get movie search
	$newinput = preg_replace('/\s+/', ' ', $raw); // replace multiple spaces with one
	$words = explode(" ", $newinput); // separate words with explode

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	 if(!$db) {
		$errmsg = mysql_error($db);
		 print "Connection failed: $errmsg <br />";
		 exit(1);
	 }
	mysql_select_db("CS143", $db);
	
	// for each word, call mysql_real_escape_string
	// 		and append it to the input array
	$input = array();
	foreach ($words as $w){
		array_push($input, mysql_real_escape_string($w));
	}
	
	// query to select the actor names
	$aquery= "SELECT DISTINCT first, last, id FROM Actor WHERE first LIKE '%$input[0]%'";

	foreach($input as $t){
		$aquery .= " OR first LIKE '%$t%' OR last LIKE '%$t%'";
	}
	$aquery .= " ORDER BY first, last";

	// query 
	$result = mysql_query($aquery, $db);

	// if there's a result, display the movies and links
	if($result && mysql_num_rows($result) > 0){
		echo "<br/><b>Actors: </b><p>";
		
		// bordered box to display results (so it doesn't get ugly)
		echo "<div style=\"border:1px dashed #8D6932;width:450px;height:300px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
				
		while($r = mysql_fetch_row($result)){
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
			if ($_GET["mid"]){
				$mid = $_GET["mid"];
				echo "<a href = './add.php?type=5&mid=$mid&aid=$r[2]'>";
			}else
				echo "<a href = './add.php?type=5&aid=$r[2]'>";
			echo "$r[0] $r[1]</a><br/>";
		}
		echo "</p></div>";
	} else { echo "<br/><b>No actor found with search \"$raw\"</b>"; }

	// close database	  
	mysql_close($db);
}
?>

<!-- IF THE PERSON CLICKS SEARCH DIRECTOR -->
<?php
if($_POST["sdirector"] && ($_POST["but"] == "dir")){
	$raw = $_POST["sdirector"]; // get movie search
	$newinput = preg_replace('/\s+/', ' ', $raw); // replace multiple spaces with one
	$words = explode(" ", $newinput); // separate words with explode

	// establish connection
	$db = mysql_connect("localhost", "cs143", "");
	 if(!$db) {
		$errmsg = mysql_error($db);
		 print "Connection failed: $errmsg <br />";
		 exit(1);
	 }
	mysql_select_db("CS143", $db);
	
	// for each word, call mysql_real_escape_string
	// 		and append it to the input array
	$input = array();
	foreach ($words as $w){
		array_push($input, mysql_real_escape_string($w));
	}
	
	// query to select the actor names
	$aquery= "SELECT DISTINCT first, last, id FROM Director WHERE first LIKE '%$input[0]%'";

	foreach($input as $t){
		$aquery .= " OR first LIKE '%$t%' OR last LIKE '%$t%'";
	}
	$aquery .= " ORDER BY first, last";

	// query 
	$result = mysql_query($aquery, $db);

	// if there's a result, display the movies and links
	if($result && mysql_num_rows($result) > 0){
		echo "<br/><b>Directors: </b><p>";	
		
		// bordered box to display results (so it doesn't get ugly)
		echo "<div style=\"border:1px dashed #8D6932;width:450px;height:300px;overflow:auto;overflow-y:scroll;overflow-x:hidden;text-align:left\" ><p>";
				
		while($r = mysql_fetch_row($result)){
			echo "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
			if ($_GET["mid"]){
				$mid = $_GET["mid"];
				echo "<a href = './add.php?type=5&mid=$mid&did=$r[2]'>";
			} else
				echo "<a href = './add.php?type=5&did=$r[2]'>";
			echo "$r[0] $r[1]</a><br/>";
		}
		echo "</p></div>";
	} else { echo "<br/><b>No director found with search \"$raw\"</b>"; }

	// close database	  
	mysql_close($db);
}
?>


<!-- RELATION FORM -->
<?php if ($type == "5") { ?>

<p>---------------------------------------------------------------------------------------------</p>

<p>
Search a movie: <br/>
<form method="POST">
<input type="text" name="smovie" placeholder="Search movie!" />
<input type="hidden" name="but" value="mov" />
<input type="submit" value="Search Movies" />
</form> 
</p>


<p>
Search an actor: <br/>
<form method="POST">
<input type="text" name="sactor" placeholder="Search actor!"/>
<input type="hidden" name="but" value="act" />
<input type="submit" value="Search Actors" />
</form>
</p>

<p>
Search an director: <br/>
<form method="POST">
<input type="text" name="sdirector" placeholder="Search director!"/>
<input type="hidden" name="but" value="dir" />
<input type="submit" value="Search Directors" />
</form>
</p>


<?php }?>
<!-- END RELATION FORM -->



<?php
/* Add Actor or Director into the database */
if(($type == "1" || $type == "2") && $_POST["first"] && 
	$_POST["last"] && $_POST["doby"] && $_POST["dobm"] && $_POST["dobd"]){
	
	$firsttemp = $_POST["first"]; // first name
	$lasttemp = $_POST["last"]; // last name
	$sex = $_POST["sex"]; // gender
	
	// date of birth
	$dobd = $_POST["dobd"]; // day
	$dobm = $_POST["dobm"]; // month
	$doby = $_POST["doby"]; // year
	$dob = "$doby-$dobm-$dobd"; // formatted for sql
	
	//date of death
	$dodd = $_POST["dodd"]; // day
	$dodm = $_POST["dodm"]; // month
	$dody = $_POST["dody"]; // year
	$dod = "$dody-$dodm-$dodd"; // formatted for sql
	$dodflag = 0; // flag to see if dod was inputted
	
	$addpersonq = ""; // blank query for now (determine later)
	
	if ($dodd > 0 && $dodm > 0 && $dody > 9){
		$dodflag = 1; // set dod flag so that we add the dod
	}

	//echo "Born on $dob! <br/>";
	//echo "Died on $dod! <br/>";
	//echo $dob;

	// a bunch of checks to make sure dates are valid
	
	if (str_replace(" ", "", $firsttemp) == ""){ // no first name
		echo "Please enter a first name! "; 
	} elseif (str_replace(" ", "", $lasttemp) == ""){ // no last name
		echo "Please enter a last name! ";
	} elseif (($dody>0 && ($dodm==0 || $dodd==0)) ||
				$dodm>0 && ($dody==0 || $dodd==0) ||
				$dodd>0 && ($dody==0 || $dodm==0)){
		echo "Enter a valid death date, or leave it blank! ";
	} elseif ($dody != 0 && $dodm != 0 && $dodd != 0 && 
			(strtotime($dod) < strtotime($dob))){ // if dob > dod
		echo "Enter a valid death date! A person can't die before he's born! ";
	} elseif (($doby%4!=0 && $dobm == 2 && $dobd > 28) ||
			  ($doby%4==0 && $dobm == 2 && $dobd > 29)) { // leap year for February
		echo "$dobm - $dobd - $doby is not a valid date!";
	} elseif (($dobm==4 || $dobm==6 || $dobm==9 || $dobm==11) && $dobd > 30) {
		// make sure days in each month is valid
		echo "$dobm - $dobd - $doby is not a valid date!";
	} elseif (($dody%4!=0 && $dodm == 2 && $dodd > 28) ||
			  ($dody%4==0 && $dodm == 2 && $dodd > 29)) { // leap year for February
		echo "$dodm - $dodd - $dody is not a valid date!";
	} elseif (($dodm==4 || $dodm==6 || $dodm==9 || $dodm==11) && $dodd > 30) {
		// make sure days in each month is valid
		echo "$dodm - $dodd - $dody is not a valid date!";
	} else {

		// create db connection
		$db = mysql_connect("localhost", "cs143", "");
		if(!$db) {
			$errmsg = mysql_error($db);
			print "Connection failed: $errmsg <br />";
			exit(1);
		}					
		mysql_select_db("CS143", $db);
		
		// escape apostrophes 
		$first = mysql_real_escape_string($firsttemp);
		$last = mysql_real_escape_string($lasttemp);
		
		// get the id of latest/ largest personID
		$pidquery = "SELECT id FROM MaxPersonID";
		$pidsearch = mysql_query($pidquery, $db);
		$pidfinished = mysql_fetch_row($pidsearch);
		$pid = $pidfinished[0];

		// FIXME: date is not adding correctly
		// add information to actor
		if ($type == "1")
			if ($dodflag == 0)
				$addpersonq = "INSERT INTO Actor VALUES ($pid, '$last', '$first', '$sex', '$dob')";
			else	 
				$addpersonq = "INSERT INTO Actor VALUES ($pid, '$last', '$first', '$sex', '$dob', '$dod')";
		elseif ($type == "2") 
			// person is a director
			if($dodflag == 0)	
				$addpersonq = "INSERT INTO Director VALUES ($pid, '$last', '$first', '$dob')";
			else
				$addpersonq = "INSERT INTO Director VALUES ($pid, '$last', '$first', '$dob', '$dod')";

		echo "<p>";
		if(mysql_query($addpersonq, $db)){
			echo "Successfully added person. <br/>";
			echo "Name is: $firsttemp $lasttemp<br/>";
			// update max id counter
			mysql_query("UPDATE MaxPersonID SET id=id+1", $db);
			echo "<br>View your profile <a href='";
			if($type=="1") // if actor or director
				echo "./actors.php?id=$pid'>";
			else echo "./directors.php?id=$pid'>";
				echo "here</a> ";
		} else { echo "Adding person unsuccessful. "; }
		echo "</p>";



		// close database	  
		mysql_close($db);
	}
}
// checks to see if you click the submit but dont have the required input for actor or director
elseif (($type=="1" || $type=="2") && $_POST["clicked"] && (!$_POST["first"] || !$_POST["last"] || 
		$_POST["doby"]=="0" || $_POST["dobm"]=="0" || $_POST["dobd"]=="0")){
	if (!$_POST["first"])
		echo "Please enter a first name! <br/>";
	if (!$_POST["last"])
		echo "Please enter a last name! <br/>";
	if ($_POST["doby"]=="0" || $_POST["dobm"]=="0" || $_POST["dobd"]=="0")
		echo "Please enter a valid birthday! ";
}

?>


<!-- form to reset -->
<p>
<form action="./add.php?type=" method="GET">
<input type="hidden" name="type" value="<?php echo $_GET["type"] ?>";/>
<input type="submit" value="Reset form" />
</form></p>

<?php /*if($type == "4") { ?>
<form action="./add.php?type=" method="GET">
<input type="hidden" name="type" value="<?php echo $_GET["type"] ?>";/>
<input type="submit" value="Add movie without director" />
</form></p>
<?php }*/ ?>

<?php } // type if statement (very first one) ?>




</body>
</html>