<?php 

include("database.php");

// The POST method is used for adding information.
if(isset($_POST['title'])) {

		



    
	// Extract the right values

	$title = $_POST['title'];
	$year = $_POST['year'];
	$rating = $_POST['rating'];
	$company = $_POST['company'];

	// Create the database connection and insert 
 	$db = dbConnect();
	
	// Figure out the next id to add
	$movieid_query = "SELECT max(id) FROM MaxMovieID";
	
	$movie_result = mysqli_query($db, $movieid_query);
	

	if($movie_result) {
		while($row = mysqli_fetch_array($movie_result)){		
			$id = $row["max(id)"];

			
		}
	
	}
	$id++;
	
	// Increase the count for MaxMovieID
	$increase_count_query = "INSERT INTO MaxMovieID VALUES ($id)";
	mysqli_query($db, $increase_count_query);

	$query = sprintf("INSERT INTO Movie VALUES (%s, '%s', '%s', '%s','%s')", mysqli_real_escape_string($db,$id),
										mysqli_real_escape_string($db,$title), 
										mysqli_real_escape_string($db,$year),
										mysqli_real_escape_string($db,$rating),
										mysqli_real_escape_string($db,$company));
	
	

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


	$query = sprintf("SELECT * FROM Movie WHERE id = %s", mysqli_real_escape_string($db, $id));

	$query_error_array = array("error" => "No query was found");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));
	
	while($row = mysqli_fetch_array($result)) {
	
//		echo $row['title'];
		echo json_encode($row);
	}


	mysqli_close($db);

}

?>


