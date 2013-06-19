<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="style.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div id="topbar">
<div id="TopSection">
<h1 id="sitename"><span><a href="#">IM</a></span><a href="#"><u>AFAKE</u></a>
<h1 id="sitename"><span>DB</span>
</h1>
<div id="topbarnav"> <span class="topnavitems"></span>


<form action="./search.php" method="POST" target="main"><div class="searchform">
<label for="searchtxt">
Search Movies/Actors/Directors:
</label>
<input type="text" name="search" onclick="document.form1.type.value ='';" placeholder="Search!"/>
<input type="submit" value="Search" />
</div> </form></div>


<div class="clear"></div>
<ul id="topmenu">
<?php // FIXME .. somehow find a way to set it as active if it's clicked?>
<li <?php if($_GET["try"]) echo"class='active'" ?>>
<!--<form id="test" method="GET">
<input type="hidden" name="try" value="me" />
</form>-->
<form name="test" method="GET" action="<?php echo $PHP_SELF;?>">
<a href="./search.php" target="main" onclick="javascript: document.test.submit();";>Search Results</a></li>
</form>
<li><a href="./actors.php" target="main">View Actors</a></li>
<li><a href="./directors.php" target="main">View Directors</a></li>
<li><a href="./movies.php" target="main">View Movies</a></li>
<li><a href="./add.php" target="main">Add Content</a></li>
<li><a href="./comment.php" target="main">Add Comment</a></li>
</ul>
</div>
</div>

</div>
</div></div>


<?php if($_GET["test"]) echo "<p> hello world! <p> hello world!<p> hello world!<p> hello world!<p> hello world!<p> hello world!"; ?>

</body>
</html>

