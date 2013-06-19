<?php 

include("database.php");

// The POST method is used for adding information.
if(isset($_POST['target']) && isset($_POST['last']) && isset($_POST['first'])) {

		



    
	// Extract the right values

	$last = $_POST['last'];
	$first = $_POST['first'];
	$sex = $_POST['sex'];
	$dob = $_POST['dob'];
	$dod = $_POST['dod'];

	// Create the database connection and insert 
 	$db = dbConnect();
	
	// Figure out the next id to add
	$personid_query = "SELECT max(id) FROM MaxPersonID";
	
	$person_result = mysqli_query($db, $personid_query);
	

	if($person_result) {
		while($row = mysqli_fetch_array($person_result)){		
			$id = $row["max(id)"];

			
		}
	
	}
	$id++;
	
	// Increase the count for MaxPersonID
	$increase_count_query = "UPDATE MaxPersonID SET id = " . $id;
	mysqli_query($db, $increase_count_query);

	if ($_POST['target'] == 'actor')
	{
		$query = sprintf("INSERT INTO Actor VALUES (%s, '%s', '%s', '%s','%s',%s)", mysqli_real_escape_string($db,$id),
										mysqli_real_escape_string($db,$last), 
										mysqli_real_escape_string($db,$first),
										mysqli_real_escape_string($db,$sex),
										mysqli_real_escape_string($db,$dob),
mysqli_real_escape_string($db,$dod));

	} elseif ($_POST['target'] == 'director')
	{
		$query = sprintf("INSERT INTO Director VALUES (%s, '%s', '%s', '%s',%s)", mysqli_real_escape_string($db,$id),
										mysqli_real_escape_string($db,$last), 
										mysqli_real_escape_string($db,$first),
										mysqli_real_escape_string($db,$dob),
mysqli_real_escape_string($db,$dod));
	} else
	{
		die(json_encode(array("error" => "Invalid target.")));
	}
	
	

	$query_error_array = array("error" => "Error inserting into database.");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));

	$success_array = array("status" => "success");
	echo json_encode($success_array);


}else{

	// Check that we have a id variable specified in the GET request
	if(!isset($_GET['id'])) {
		
		// If the id is empty then we have no results.
		$error_array = array("error" => "No id was found");	
		die(json_encode($error_array));

	}

    
	    // Try to connect
	    $db = dbConnect();
    
	// Look for all results with the same id
	$id = $_GET['id'];

	if (isset($_GET['target']))
	{	
		if ($_GET['target'] == 'actor')
		{
			$query1 = sprintf("SELECT * FROM Actor WHERE id = %s", mysqli_real_escape_string($db, $id));
			$query2 = sprintf("SELECT * FROM MovieActor, Movie WHERE aid = %s AND MovieActor.mid = Movie.id ORDER BY year DESC", mysqli_real_escape_string($db, $id));
		} elseif ($_GET['target'] == 'director')
		{
			$query1 = sprintf("SELECT * FROM Director WHERE id = %s", mysqli_real_escape_string($db, $id));
			$query2 = sprintf("SELECT * FROM MovieDirector, Movie WHERE did = %s AND MovieDirector.mid = Movie.id ORDER BY year DESC", mysqli_real_escape_string($db, $id));
		} else
		{
			die(json_encode(array("error"=>"Invalid target.")));
		}

		$query_error_array = array("error" => "No query was found");	
		$result1 = mysqli_query($db, $query1) or die(json_encode($query_error_array));
		$result2 = mysqli_query($db, $query2) or die(json_encode($query_error_array));

		$arr = array();
		$suba = array();
	
		while($row = mysqli_fetch_array($result1)) {
	
//		echo $row['title'];
			array_push($suba,$row);
		}

		array_push($arr, array("person" => $suba));
		$suba = array();

		while ($row = mysqli_fetch_array($result2))
		{
			array_push($suba,$row);
		}
		array_push($arr, array("movie" => $suba));
		echo json_encode($arr);
	}	


	mysqli_close($db);

}

?>


