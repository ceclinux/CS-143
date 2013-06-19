<?php
include 'search.php';

function connect()
{
	$db_connection = mysql_connect("localhost", "cs143", "");
	mysql_select_db("CS143", $db_connection);
	return $db_connection;
}

function disconnect($db_connection)
{
	mysql_close($db_connection);
}

function printResult($rs)
{
	if ($rs == NULL)
		return;

	echo "<table border=1>";
	foreach ($rs as $elem)
	{
		echo "<tr>";
		foreach ($elem as $e)
		{
			echo "<td>" . $e . "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}

function test()
{
	if (isset($_GET) && isset($_GET["keywords"]) && isset($_GET["target"]))
	{
		$db = connect();

		$json = search($_GET["keywords"], $_GET["target"], $db);
	 	
		$arr = json_decode($json, true);

		if (isset($arr["movie"]))
		{
			$m = $arr["movie"];
			echo "<h3>Movies</h3>";
			$me = $m["exact"];
			echo "<b>Exact Results:</b><br />";
			printResult($me);
			$mc = $m["close"];
			echo "<b>Close Results:</b><br />";
			printResult($mc);
			$mp = $m["partial"];
			echo "<b>Partial Results:</b><br />";
			printResult($mp);
		} 

		if (isset($arr["actor"]))
		{
			$a = $arr["actor"];
			echo "<h3>Actors</h3>";
			$ae = $a["exact"];
			echo "<b>Exact Results:</b><br />";
			printResult($ae);
			$ap = $a["partial"];
			echo "<b>Partial Results:</b><br />";
			printResult($ap);

		}

		if (isset($arr["director"]))
		{
			$d = $arr["director"];
			echo "<h3>Directors</h3>";
			$de = $d["exact"];
			echo "<b>Exact Results:</b><br />";
			printResult($de);
			$dp = $d["partial"];
			echo "<b>Partial Results:</b><br />";
			printResult($dp);

		}

		disconnect($db);
	}
}
?>

<html>
<head><title>Search Test</title></head>
<body>
<form action="" method="GET">
<select name="target">
	<option value="all">All</option>
	<option value="movie">Movies</option>
	<option value="actor">Actors</option>
	<option value="director">Directors</option>
	<option value="people">People</option>
</select>
<input type="text" name="keywords" />
<input type="submit" value="Search" />
</form>
<br />
</body>
</html>

<?php
test();
?>
