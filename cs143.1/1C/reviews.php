<?php 

include("database.php");



/* POST parameters:
	name: name of the reviewer
	mid: movie id
	rating: rating of the movie
	comment: review comment

   GET parameters:
	mid: id of the Movie of interest.
	

*/



// The POST method is used for adding information.
if(isset($_POST['mid'])) {

		
	// Extract the right values

	$name = $_POST['name'];

	// Get the current timestamp 
	$time = "";

	$mid= $_POST['mid'];
	$rating = $_POST['rating'];
	
	$comment = $_POST['comment'];

	// Create the database connection and insert 
 	$db = dbConnect();
	
	
	$query = sprintf("INSERT INTO Review VALUES ('%s', NULL, %s, %s,'%s')", mysqli_real_escape_string($db,$name),
										mysqli_real_escape_string($db,$mid), 
										mysqli_real_escape_string($db,$rating),
										mysqli_real_escape_string($db,$comment));

	
//	echo $query;	

	$query_error_array = array("error" => "Error inserting into database.");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));

	$success_array = array("status" => "success");
	echo json_encode($success_array);


}else{

	// Check that we have a id variable specified in the GET request
	if(!isset($_GET['mid'])) {
		
		// If the id is empty then we have no results.
		$error_array = array("error" => "No id was found");	
		die(json_encode($error_array));

	}

    
	    // Try to connect
	    $db = dbConnect();
    
	// Look for all results with the same id
	$id = $_GET['mid'];


	$query = sprintf("SELECT * FROM Review WHERE mid = %s", mysqli_real_escape_string($db, $id));

	$query_error_array = array("error" => "No query was found");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));
	

	$retarr = array();
	$comments = array();

	while($row = mysqli_fetch_array($result)) {
	
//		echo $row['title'];
//		echo json_encode($row);
		array_push($comments, $row);

	}

	array_push($retarr, $comments);

	
	$query = sprintf("SELECT AVG(rating) FROM Review WHERE mid = %s", mysqli_real_escape_string($db, $id));

	$query_error_array = array("error" => "No query was found");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));
	
	while($row = mysqli_fetch_array($result)) {
	
//		echo $row['title'];
		array_push($retarr, $row);

	}

	echo json_encode($retarr);


	mysqli_close($db);

}

?>


