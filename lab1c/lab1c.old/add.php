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
<li class="active"><a href="add.php">Add Content</a></li>
<li><a href="comment.php">Add Comment</a></li>
</ul>
</div>
</div>

<div id="wrap">
<div id="contents">
<div id="left">

<form method="GET">

<select name="type">
<option value="0">PLEASE SELECT ONE</option>
<option value="1">Add Actor</option>
<option value="2">Add Director</option>
<option value="4">Add Movie </option>
<option value="5">Add Actor/Movie relation</option>
</select>

<input type="submit" value="Change Form" />
</form>
</font>
</body>
</html>

<?php
if($_GET["type"]){
	$type = $_GET["type"];

	// form for adding actor or director
	if ($type == "1" || $type == "2"){
		?>
<p>
<form method="post">
<input type="hidden" name="clicked" />
<p>First: <input type="text" name="first" /><br/></p>
<p>Last: <input type="text" name="last" /><br/></p>
<p>Gender: <input type="radio" name="sex" value="Male" checked/> Male 
<input type="radio" name="sex" value="Female"/> Female <br/></p>
<p>Date of Birth:<input type="text" name="dob" /> (YYYY-MM-DD)<br/></p>
<p>Date of Death (if Applicable) <input type="text" name"dod" /> (YYYY-MM-DD)<br/></p>
<p><input type="submit" value="Add Person"/></p>
</form>
</p>

<?php
// adding a movie
}

if ($type == "4"){
?>
<p>
<form method="post">
Add a new movie: <br/>
<input type="hidden" name="clicked" />
<p>Title: <input type="text" name="title" /><br/></p>
<p>Company: <input type="text" name="company" /><br/></p>
<p>Year: <input type="text" name="year" /><br/></p>
<p>Director: 

<?php // FIXME check to see if there's a director id or not
// if it isn't, let them search for one 
if ($_GET["did"]){
	$did = $_GET["did"];
	
	// establish connection
     $db = mysql_connect("localhost", "cs143", "");
      if(!$db) {
         $errmsg = mysql_error($db);
          print "Connection failed: $errmsg <br />";
          exit(1);
      }
	mysql_select_db("CS143", $db);

	//get the director
	$direct = "SELECT first, last, id FROM Director WHERE id = $did";

	// get the results
	$result = mysql_query($direct, $db);
	
	if($result && mysql_num_rows($result) > 0){
		$r = mysql_fetch_row($result);
		echo "<b>$r[0] $r[1] </b><br/>";
		
	}

//} else {
// ELSE LET THEM SEARCH
?>
<form method="post">
<input type="text" name="director" placeholder="Search a name!"/>
<input type="hidden" name="clicked" value="dirsearch"/>
<input type="submit" value="Search!" /></p>
</form>
<?php } 
//FIXME implement this search
if ($type == "4" && ($_POST["director"]) && ($_POST["clicked"] == "dirsearch")){
	$raw = $_POST["director"];
	$input = explode(" ", $raw);


	// establish connection
     $db = mysql_connect("localhost", "cs143", "");
      if(!$db) {
         $errmsg = mysql_error($db);
          print "Connection failed: $errmsg <br />";
          exit(1);
    }
	mysql_select_db("CS143", $db);
	// query to search directors
	$dquery = "SELECT DISTINCT first, last, id FROM Director WHERE first LIKE '%$input[0]%'";

	foreach($input as $d){
		$dquery .= " OR first LIKE '%$d%' OR last LIKE '%$d%'";
	}

	$dquery .= " ORDER BY first, last";
	
	// query
	$result = mysql_query($dquery, $db);

	// if there's a result then output the director names
	if ($result && mysql_num_rows($result) > 0){
		echo "<p>Directors: <br/>";
		while ($r = mysql_fetch_row($result)){
			echo "<a href='./add.php?type=4&did=$r[2]'>";
			echo "$r[0] $r[1] </a><br/>";
		} echo "</p>";
	} else { echo "No director found, please search again!" ; }

	// close database
	mysql_close($db);
}

?>
<p>MPAA Rating: 
<select name="MPAA">
<option value="g">G</option>
<option value="nc17">NC-17</option>
<option value="pg">PG</option>
<option value="pg13">PG-13</option>
<option value="r">R</option>
</select></p><br/>
Genre (check all that apply): <br/>
<?php
$genres = array("Action", "Adult", "Adventure", "Animation", "Comedy", "Crime",
		"Documentary", "Drama", "Family", "Fantasy", "Horror", "Musical", "Mystery",
		"Romance", "Sci-Fi", "Short", "Thriller", "War", "Western");

function checks($val){
	echo "<input type=\"checkbox\" name=\"genre\" value=".$val."/>" .$val. "<br/> ";
}

foreach ($genres as $gen)
	checks($gen);
?>

<?php if ($type == "4" && $_GET["did"]){ 
// only show submit button after a director is chosen?>
<p><input type="submit" value="Add Movie"/></p>
<?php } ?>
</form>
</p>


<?php
// form for adding actor/movie relation
}

if ($type == "5" && $_GET["mid"]){
	$mid = $_GET["mid"];

	// create db connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}					

	mysql_select_db("CS143", $db_connection);

	// get movie title
	$qTitle = "SELECT title FROM Movie WHERE id = $mid";
	$title = mysql_query($qTitle, $db_connection);

	if ($title){
		$r = mysql_fetch_row($title);
		echo "<h3>Movie : $r[0]</h3>";
	}

	mysql_close($db_connection);	
}?>


<?php

if ($type == "5" && $_GET["aid"]){
     $aid = $_GET["aid"];
 
     // create db connection
	$db_connection = mysql_connect("localhost", "cs143", "");
    if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
	    exit(1);
    }
    mysql_select_db("CS143", $db_connection);
 
    // get movie title
    $qName = "SELECT first, last FROM Actor WHERE id = $aid";
    $title = mysql_query($qName, $db_connection);

    if ($title){
        $r = mysql_fetch_row($title);
        echo "<h3>Actor : $r[0] $r[1]</h3>";
    }
 
     mysql_close($db_connection);
 } 
?>

<?php if ($type == "5" && $_GET["mid"] && $_GET["aid"]){ ?>

<form method = "POST">
<h3>Role <input type="text" name="role"/></h3>
<p>Do you wish to add a relationship between these two?
<input type="hidden" name="select" value="add"/>
<input type="submit" value="Add Relation!"/></p>
</form>
<?php } // end bracket for if it's showing a pair?>

<?php
// if user decides to add relation to the movies
if ($type == "5" && $_POST["select"] == "add" && $_POST["role"]){
	$mid = $_GET["mid"];
	$aid = $_GET["aid"];
	$temprole = $_POST["role"];

	 // establish connection
    $db_connection = mysql_connect("localhost", "cs143", "");
     if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
         print "Connection failed: $errmsg <br />";
         exit(1);
     }

	mysql_select_db("CS143", $db_connection);

	$role = mysql_real_escape_string($temprole);
	
	// add query	
	$relation = "INSERT INTO MovieActor VALUES ($mid, $aid, '$role')";

	// if/not sucessfully added into database
	if (mysql_query($relation, $db_connection)){
		echo "<p><b><u>Sucessfully added into relation table! </b></u>";
		echo "<br/>View your new relation <a href='./movies.php?id=$mid'>here</a></p>";
	} else { echo "Something went wrong."; }

	//close connection
	mysql_close($db_connection);
}
?>

<?php
// searching a movie for movie/actor relation
if($_POST["smovie"] && ($_POST["but"] == "mov")){
	$raw = $_POST["smovie"];
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
	if($result && mysql_num_rows($result) > 0){
		echo "<b>Movies: </b><p>";
		while($r = mysql_fetch_row($result)){
			if ($_GET["aid"]){
				$aid = $_GET["aid"];
				echo "<a href = './add.php?type=5&aid=$aid&mid=$r[1]'>";
			}else 
				echo "<a href = './add.php?type=5&mid=$r[1]'>";
			echo "" .$r[0]. "</a><br/></p>";
		}
	} else { echo "No movie with \"" .$input. "\""; }

		// close database   
		mysql_close($db_connection);
}
?>


<?php
if($_POST["sactor"] && ($_POST["but"] == "act")){
	$raw = $_POST["sactor"];
	$input = explode(" ", $raw);

	 // establish connection
    $db_connection = mysql_connect("localhost", "cs143", "");
     if(!$db_connection) {
        $errmsg = mysql_error($db_connection);
         print "Connection failed: $errmsg <br />";
         exit(1);
     }

	mysql_select_db("CS143", $db_connection);
	
	// query to select the actor names
	$aquery= "SELECT DISTINCT first, last, id FROM Actor WHERE first LIKE '%$input[0]%'";

	foreach($input as $t){
		$aquery .= " OR first LIKE '%$t%' OR last LIKE '%$t%'";
	}
	$aquery .= " ORDER BY first, last";

	// query 
	$result = mysql_query($aquery, $db_connection);

	// if there's a result, display the movies and links
	if($result && mysql_num_rows($result) > 0){
		echo "<b>Actors: </b><p>";
		while($r = mysql_fetch_row($result)){
			if ($_GET["mid"]){
				$mid = $_GET["mid"];
				echo "<a href = './add.php?type=5&mid=$mid&aid=$r[2]'>";
			}else
				echo "<a href = './add.php?type=5&aid=$r[2]'>";
			echo "$r[0] $r[1]</a><br/></p>";
		}
	} else { echo "No actor with \"$input\""; }

		// close database   
		mysql_close($db_connection);
}
?>




<?php if ($type == "5") { ?>
<p>
Search a movie: <br/>
<form method="POST">
<input type="text" name="smovie" />
<input type="hidden" name="but" value="mov" />
<input type="submit" value="Search Movies" />
</form> 
</p>


<p>
Search an actor: <br/>
<form method="POST">
<input type="text" name="sactor" />
<input type="hidden" name="but" value="act" />
<input type="submit" value="Search Actors" />
</form>
</p>
<?php }?>






<?php

// FOR INPUTTING A NEW PERSON
if(($type == "1" || $type == "2") && $_POST["first"] && 
	$_POST["last"] && $_POST["dob"]){
	$first = $_POST["first"];
	$last = $_POST["last"];
	$dobtemp = $_POST["dob"];
	$sex = $_POST["sex"];
	$dob = strtotime($dobtemp);
	$dodflag = 0;
	$dod = "";
	
	if ($_POST["dod"]){
		echo "Hello";
		$dod = strtotime($_POST["dod"]);
		$dodflag = 1;
	}
	$newdob = date('Y-m-d', $dob);
	$addpersonq = "";

	// check to make sure inputs are not just spaces
	if (str_replace(" ", "", $first) == ""){
		echo "Please enter a first name! ";
	} elseif (str_replace(" ", "", $last) == ""){
		echo "Please enter a last name! ";
	} elseif (str_replace(" ", "", $dob) == ""){
		echo "Please enter a date of birth! ";
	} else {

		// create db connection
		$db_connection = mysql_connect("localhost", "cs143", "");
		if(!$db_connection) {
			$errmsg = mysql_error($db_connection);
			print "Connection failed: $errmsg <br />";
			exit(1);
		}					

		mysql_select_db("CS143", $db_connection);
		$pidquery = "SELECT id FROM MaxPersonID";
		$pidsearch = mysql_query($pidquery, $db_connection);

		// value of the largest personId
		$pidfinished = mysql_fetch_row($pidsearch);
		$pid = $pidfinished[0];

		echo $pid;


		// FIXME: date is not adding correctly
		// add information to actor
		if ($type == "1")
			if ($dodflag == 0)
				$addpersonq = "INSERT INTO Actor VALUES ($pid, '$last', '$first', '$sex', $dob,'\N')";
			else  
				$addpersonq = "INSERT INTO Actor VALUES ($pid, '$last', '$first', '$sex', $dob, $dod)";
		elseif ($type == "2") 
			// person is a director
			if($dodflag == 0)	
				$addpersonq = "INSERT INTO Director VALUES ($pid, '$last', '$first', $dob, '\N')";
			else
				$addpersonq = "INSERT INTO Director VALUES ($pid, '$last', '$first', $dob, $dod)";

		if(mysql_query($addpersonq, $db_connection)){
			echo "Succesfully added person. ";
			mysql_query("UPDATE MaxPersonID SET id=id+1", $db_connection);
		} else { echo "Adding person unsuccessful. "; }

		echo "Name is: $first $last, born on $newdob";


		// close database   
		mysql_close($db_connection);
	}
}
// checks to see if you click the submit but dont have the required input
elseif (($type=="1" || $type=="2") && $_POST["clicked"] && (!$_POST["first"] || !$_POST["last"] || !$_POST["dob"])){
	if (!$_POST["first"])
		echo "Please enter a first name! <br/>";
	if (!$_POST["last"])
		echo "Please enter a last name! <br/>";
	if (!$_POST["dob"])
		echo "Please enter a date of birth! <br/> ";
}

if ($type=="3" && $_POST["title"] && $_POST["company"] && $_POST["year"]){
	
}
// if hit submit but no title, company, or year
elseif ($type=="3" && $_POST["clicked"] && (!$_POST["title"] || !$_POST["company"] || !$_POST["year"])){
	if(!$_POST["title"])
		echo "Please enter a title! <br/>";
	if(!$_POST["company"])
		echo "Please enter a comapny! <br/>";
	if(!$_POST["year"])
		echo "Please eneter a year! <br/>";
}

?>


<?php
}
?>
