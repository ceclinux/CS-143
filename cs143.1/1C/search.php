<?php
/*
  $_GET keys:
	- target: the tables to search (All/Movie/Actor/Director/Person)
	- keywords: the search query

  Exact match:
	- Movie title
	- Person name:
	  1 word -> no complete matches
	  2 words -> try "first last" and "last first"
	  2+ words -> try "first last" and "last first" for each combo:
		1 2...n, 12 3...n, etc.
*/

function resultToArray($rs)
{
	$arr = array();

	if ($rs === false)
		return NULL;

	while ($row = mysqli_fetch_row($rs))
	{
		array_push($arr, $row);
	}	

	return $arr;
}

function search($keywords, $target, $db_connection)
{
	$keywords = trim($keywords);
		
	if ($target == "all")
	{
		$mexact = movieExactQuery($keywords);
		$mclose = movieCloseQuery($keywords);
		$mpartial = moviePartialQuery($keywords);

		$resme = mysqli_query($db_connection,$mexact);
		$resmc = mysqli_query($db_connection, $mclose);
		$resmp = mysqli_query($db_connection, $mpartial);

		$rme = resultToArray($resme);
		$rmc = resultToArray($resmc);
		$rmp = resultToArray($resmp);

		$aexact = peopleExactQuery($keywords, "actor");
		$apartial = peoplePartialQuery($keywords, "actor");

		$resae = mysqli_query($db_connection, $aexact);
		$resap = mysqli_query($db_connection, $apartial);

		$rae = resultToArray($resae);
		$rap = resultToArray($resap);

		$dexact = peopleExactQuery($keywords, "director");
		$dpartial = peoplePartialQuery($keywords, "director");

		$resde = mysqli_query($db_connection, $dexact);
		$resdp = mysqli_query($db_connection, $dpartial);

		$rde = resultToArray($resde);
		$rdp = resultToArray($resdp);

		$arr = array("movie" => array("exact" => $rme,
			"close" => $rmc, "partial" => $rmp),
			"actor" => array("exact" => $rae,
			"partial" => $rap),
			"director" => array("exact" => $rde,
			"partial" => $rdp));
	} elseif ($target == "movie")
	{
		$exact = movieExactQuery($keywords);
		$close = movieCloseQuery($keywords);
		$partial = moviePartialQuery($keywords);

		$rese = mysqli_query($db_connection, $exact);
		$resc = mysqli_query($db_connection, $close);
		$resp = mysqli_query($db_connection, $partial);
		
		$rme = resultToArray($rese);
		$rmc = resultToArray($resc);
		$rmp = resultToArray($resp);

		$arr = array("movie" => array("exact" => $rme,
			"close" => $rmc, "partial" => $rmp));
	} elseif ($target == "actor")
	{
		$exact = peopleExactQuery($keywords, $target);
		$partial = peoplePartialQuery($keywords, $target);

		$rese = mysqli_query($db_connection, $exact);
		$resp = mysqli_query($db_connection, $partial);

		$rae = resultToArray($rese);
		$rap = resultToArray($resp);
		
		$arr = array("actor" => array("exact" => $rae,
			"partial" => $rap));
	} elseif ($target == "director")
	{
		$exact = peopleExactQuery($keywords, $target);
		$partial = peoplePartialQuery($keywords, $target);

		$rese = mysqli_query($db_connection, $exact);
		$resp = mysqli_query($db_connection, $partial);

		$rde = resultToArray($rese);
		$rdp = resultToArray($resp);
		
		$arr = array("director" => array("exact" => $rde,
			"partial" => $rdp));
	} elseif ($target == "people")
	{
		$aexact = peopleExactQuery($keywords, "actor");
		$apartial = peoplePartialQuery($keywords, "actor");

		$resae = mysqli_query($db_connection, $aexact);
		$resap = mysqli_query($db_connection, $apartial);

		$rae = resultToArray($resae);
		$rap = resultToArray($resap);

		$dexact = peopleExactQuery($keywords, "director");
		$dpartial = peoplePartialQuery($keywords, "director");

		$resde = mysqli_query($db_connection, $dexact);
		$resdp = mysqli_query($db_connection, $dpartial);

		$rde = resultToArray($resde);
		$rdp = resultToArray($resdp);

		$arr = array("actor" => array("exact" => $rae,
			"partial" => $rap),
			"director" => array("exact" => $rde,
			"partial" => $rdp));
	} else
	{
		return json_encode(array("error" => "Invalid target."));
	}

	return json_encode($arr);
}

// Returns the SQL query for finding an exact match
// The SQL query returns the id and title of the movie
function movieExactQuery($keywords)
{
	// Movies which start with "The" are stored like "Thin Red Line, The"
	// This grabs the first word and the remaining words in case the user
	// enters "The Thin Red Line"
	$r = strstr($keywords, " ");

	if ($r === false)
	{
		$rem = substr($r, 1);
		$first = substr($keywords, 0, -strlen($rem)-1);
	
		// The movie's title didn't contain any spaces (one word)
		return "SELECT id, title FROM Movie WHERE title = 
			'" . $keywords . "'";
	} else
	{
		return "SELECT id, title FROM Movie WHERE title =
			'" . $keywords . "' OR title = '" . $rem .
			", " . $first . "'";
	}
}

// Returns the movie titles that have all of the search terms in the title
function movieCloseQuery($keywords)
{
	// Break keywords into tokens delimited by space
	$token = strtok($keywords, " ");
	$numwords = 0;	

	while ($token !== false)
	{
		$numwords = $numwords + 1;
		$tokens[$numwords] = $token;
		$token = strtok(" ");
	}

	$query = "SELECT id, title FROM Movie WHERE ";

	for ($i = 1; $i <= $numwords; $i++)
	{
		/*
		$query = $query . "(title LIKE '" . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . "'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . ", %'";
		$query = $query . " OR title LIKE '" . $tokens[$i] . ", %')";
		*/
		$query = $query . "title LIKE '%". $tokens[$i] . "%'";
		if ($i != $numwords)
			$query = $query . " AND ";
	}

	$query = $query . " AND title <> '" . $keywords . "'";

	$r = strstr($keywords, " ");

	if ($r !== false)
	{
		$rem = substr($r, 1);
		$first = substr($keywords, 0, -strlen($rem)-1);
	
		$query = $query . " AND title <> '" . $rem . ", " . $first . "'";
	}

	return $query;
}

// Returns the movie titles that have any of the search terms in the title
function moviePartialQuery($keywords)
{
	// Break keywords into tokens delimited by space
	$token = strtok($keywords, " ");
	$numwords = 0;	

	while ($token !== false)
	{
		$numwords = $numwords + 1;
		$tokens[$numwords] = $token;
		$token = strtok(" ");
	}

	$query = "SELECT id, title FROM Movie WHERE (";

	for ($i = 1; $i <= $numwords; $i++)
	{
		/*
		$query = $query . "(title LIKE '" . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . "'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . ", %'";
		$query = $query . " OR title LIKE '" . $tokens[$i] . ", %')";
		*/
		$query = $query . "title LIKE '%" . $tokens[$i] . "%'";
		if ($i != $numwords)
			$query = $query . " OR ";
	}

	$query = $query . ") AND NOT (";

	for ($i = 1; $i <= $numwords; $i++)
	{
		/*
		$query = $query . "(title LIKE '" . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . " %'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . "'";
		$query = $query . " OR title LIKE '% " . $tokens[$i] . ", %'";
		$query = $query . " OR title LIKE '" . $tokens[$i] . ", %')";
		*/
		$query = $query . "title LIKE '%" . $tokens[$i] . "%'";
		if ($i != $numwords)
			$query = $query . " AND ";
	}
	
	$query = $query . ")";

	$query = $query . " AND title <> '" . $keywords . "'";

	$r = strstr($keywords, " ");

	if ($r !== false)
	{
		$rem = substr($r, 1);
		$first = substr($keywords, 0, -strlen($rem)-1);
	
		$query = $query . " AND title <> '" . $rem . ", " . $first . "'";
	}

	return $query;
}

// Returns the SQL queries for finding an exact match
// Name must be in the format "first last"
// Returns multiple queries if there are more than two words by trying to match
// first = 1...j last = j+1...n for j = 1:n-1
// Ex. Search = "Gerardo De La Cruz"
// Searches for Gerardo/De La Cruz, Gerardo De/La Cruz, Gerardo De La/Cruz
function peopleExactQuery($keywords, $target)
{
	// Allow input in the format last, first
	// This grabs the first name and the last name from that format
	$f = strstr($keywords, ",");

	// If the user entered last, first
	if ($f !== false)
	{
		$first = substr($f, 2);
		$last = substr($keywords, 0, -strlen($first)-2);
		
		if ($target == "actor")
		{
			return "SELECT id, first, last FROM Actor 
				WHERE first = '" . $first . "' AND last = '"
				 . $last . "'";
		} elseif ($target == "director")
		{
			return "SELECT id, first, last FROM Director 
				WHERE first = '" . $first . "' AND last = '"
				 . $last . "'";
		}
	}

	// Break keywords into tokens delimited by space

	$token = strtok($keywords, " ");
	$numwords = 0;	

	while ($token !== false)
	{
		$numwords = $numwords + 1;
		$tokens[$numwords] = $token;
		$token = strtok(" ");
	}

	if ($target == "actor")
	{
		$query = "SELECT id, first, last FROM Actor WHERE ";
	} elseif ($target == "director")
	{
		$query = "SELECT id, first, last FROM Director WHERE ";
	}

	for ($i = 1; $i < $numwords; $i++)
	{
		// Form first and last names
		$first = "";

		for ($j = 1; $j <= $i; $j++)
		{
			$first = $first . $tokens[$j] . " ";
		}

		$last = "";

		for ($k = $i + 1; $k <= $numwords; $k++)
		{
			$last = $last . $tokens[$k] . " ";
		}

		// Remove trailing space
		$first = substr($first, 0, -1);
		$last = substr($last, 0, -1);

		$query = $query . "(first = '" . $first . "' AND ";
		$query = $query . "last = '" . $last . "')";

		if ($i < $numwords - 1)
		{
			$query = $query . " OR ";
		}	
	}

	return $query;
}

function peoplePartialQuery($keywords, $target)
{
	// Allow input in the format last, first
	// This grabs the first name and the last name from that format
	$f = strstr($keywords, ",");

	// If the user entered last, first
	if ($f !== false)
	{
		$first = substr($f, 2);
		$last = substr($keywords, 0, -strlen($first)-2);
		
		if ($target == "actor")
		{
			return "SELECT id, first, last FROM Actor 
				WHERE (first = '" . $first . "' OR last = '"
				 . $last . "') AND NOT (first = '" . $first
				. "' AND last = '" . $last . "')";
		} elseif ($target == "director")
		{
			return "SELECT id, first, last FROM Director 
				WHERE (first = '" . $first . "' OR last = '"
				 . $last . "') AND NOT (first = '" . $first
				. "' AND last = '" . $last ."')";
		}
	}

	// Break keywords into tokens delimited by space

	$token = strtok($keywords, " ");
	$numwords = 0;	

	while ($token !== false)
	{
		$numwords = $numwords + 1;
		$tokens[$numwords] = $token;
		$token = strtok(" ");
	}

	if ($target == "actor")
	{
		$query = "SELECT id, first, last FROM Actor WHERE ";
	} elseif ($target == "director")
	{
		$query = "SELECT id, first, last FROM Director WHERE ";
	}

	for ($i = 1; $i < $numwords; $i++)
	{
		// Form first and last names
		$first = "";

		for ($j = 1; $j <= $i; $j++)
		{
			$first = $first . $tokens[$j] . " ";
		}

		$last = "";

		for ($k = $i + 1; $k <= $numwords; $k++)
		{
			$last = $last . $tokens[$k] . " ";
		}

		// Remove trailing space
		$first = substr($first, 0, -1);
		$last = substr($last, 0, -1);

		$query = $query . "((first = '" . $first . "' OR ";
		$query = $query . "last = '" . $last . "') AND NOT (";
		$query = $query . "first = '" . $first . "' AND ";
		$query = $query . "last = '" . $last . "'))";

		if ($i < $numwords - 1)
		{
			$query = $query . " OR ";
		}	
	}
	
	if ($numwords > 1)
		$query = $query . " OR ";

	$query = $query . "(first = '" . $keywords . "' OR ";
	$query = $query . "last = '" . $keywords . "')";

	return $query;
}

/* Actual functionality coded to match Terry's
*/

if (isset($_GET) && isset($_GET["target"]) && isset($_GET["keywords"]))
{
	include("database.php");

	$db = dbConnect();

	echo search($_GET["keywords"], $_GET["target"], $db);	

	mysqli_close($db);
}
?>
