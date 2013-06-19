
<html>
<head><title>CS143 Project 1B by Jonathan Nguy</title></head>
<body>
Type an SQL query in the following box:
<p>
<form /*action="."*/ method="GET">
<textarea name="query" cols="60" rows="8"></textarea>
<input type="submit" value="Submit" />
</form>
</p>
<p><small>Note: tables and fields are case sensitive. Run "show tables" to see the list of
available tables.</small>
</p>

<?php
// get the input
if($_GET["query"]){
	$input = $_GET["query"];

	// establish connection
	$db_connection = mysql_connect("localhost", "cs143", "");
	if(!$db_connection) {
		$errmsg = mysql_error($db_connection);
		print "Connection failed: $errmsg <br />";
		exit(1);
	}

	// sanitize
	//$sanquery = mysql_real_escape_string($input, $db_connection);

	// input
	$query = $input;
	
	// select database
	mysql_select_db("CS143", $db_connection);

	//$query = $sanquery;
	echo "Your query: ".$query." <br/>";
	echo "<h3>Results from MySQL:</h3>";

	//$query = "SELECT id, last, first, sex, dob, dod FROM Actor WHERE id>=120 AND id<=200;";
	$result = mysql_query($query, $db_connection);

	# check if valid query
	if (!$result){
		die('Could not query! WTH<br/>' . mysql_error());
	}

	$i = 0;
	echo '<table border=1 cellspacing=1 cellpadding=2><tr>';
	while ($i < mysql_num_fields($result)){
		$meta = mysql_fetch_field($result, $i);
		echo '<td><b>' . $meta->name . '</b></td>';
		$i = $i + 1;
	}
	echo '<tr>';

	$x = 0;

# read a row
	while ($row = mysql_fetch_row($result)){
# for each element in that row
		for($x=0; $x<$i; $x++){
			if ($row[$x] == NULL){
				echo '<td>N/A</td>';}
			else{
				echo '<td>' . $row[$x] . '</td>';
			}
		}
		echo '</td><tr>';
	}

	echo '</tr></table>';

	// close database
	mysql_close($db_connection);
}
?>

</body>
</html>
