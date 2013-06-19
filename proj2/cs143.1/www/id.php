<?php 


include("database.php");


if(isset($_GET["type"])){
	$type = $_GET["type"];
	

	$db = dbConnect();


	if(strcmp($type, "movie") == 0) {
		$query = "SELECT max(id) FROM MaxMovieID";

	}else{
		$query = "SELECT max(id) FROM MaxPersonID";
	}

	$query_error_array = array("error" => "Error in getting MaxPersonID.");	
	$result = mysqli_query($db, $query) or die(json_encode($query_error_array));
	
	$value = 0;

	if($result) {
		

		while($row = mysqli_fetch_array($result)){		
			echo json_encode($row);
	
			$value = $row["max(id)"];
		}

	}



	// Add one to the Max table
	if(strcmp($type, "movie") == 0 && $value > 0) {
		
		$value++;
		$add_query = "INSERT INTO MaxMovieID VALUES ($value)";

	}else{
		$value++;
		$add_query = "INSERT INTO MaxPersonID VALUES ($value)";

	}

//	echo $add_query;

	mysqli_close($db);

}

?>