<?php

function connect()
{
	$db_connection = mysql_connect("localhost", "cs143", "");
	mysql_select_db("CS143", $db_connection);
	return $db_connection;
}

function query($query, $db_connection)
{
	$rs = mysql_query($query, $db_connection);
	return $rs;
}

function printResult($rs)
{
	echo "<table border=1>";
	while ($row = mysql_fetch_row($rs))
	{
		echo "<tr>";
		$size = sizeof($row);
		for ($i = 0; $i < $size; $i++)
		{
			echo "<td>" . $row[$i] . "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}

function disconnect($db_connection)
{
	mysql_close($db_connection);
}
?>

<html>
<head><title>Query</title></head>
<body>
<form action="" method="POST">
<textarea name="query" cols="60" rows="8"></textarea>
<br />
<input type="submit" value="Submit" />
</form>
<br />
</body>
</html>

<?php
if (isset($_POST) && isset($_POST["query"]))
{
	$db_connection = connect();
	
	if (!$db_connection)
	{
		$errmsg = mysql_error($db_connection);
		echo "Connection failed:" .  $errmsg . "<br />";
		exit(1);
	}
	
	$rs = query($_POST["query"], $db_connection);
	printResult($rs);
	
	disconnect($db_connection);
}
?>
