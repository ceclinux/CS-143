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


<form id="snow" action="./search.php" method="POST" target="main"><div class="searchform">
<label for="searchtxt">
Search Movies/Actors/Directors:
</label>
<input type="text" name="search" placeholder="Search!"/>
<input type="submit" value="Search" onclick="document.getElementById('snow').submit();" />
</div> </form></div>


<div class="clear"></div>
<ul id="topmenu">

<!--
<?php // FIXME .. somehow find a way to set it as active if it's clicked?>
<li <?php if($_GET["try"] == 1) echo "class='active'" ?>>
<form id="try1" method="GET" action="index.php">
<input type="hidden" name="try" value="1" />
<a href="./search.php" target="main" onclick="top.frames['main'].location.href ='./search.php'; document.getElementById('try1').submit();" >Search Results</a></li>

<li <?php if($_GET["try"] == 2) echo "class='active'" ?>>
</form>
<form id="try2" method="GET" action="index.php">
<input type="hidden" name="try" value="2" />
<a href="./actors.php" target="main" onclick="top.frames['main'].location.href ='./actors.php'; document.getElementById('try2').submit();" >View Actors</a></li>

<li <?php if($_GET["try"] == 3) echo "class='active'" ?>>
</form>
<form id="try3" method="GET" action="index.php">
<input type="hidden" name="try" value="3" />
<a href="./directors.php" target="main" onclick="top.frames['main'].location.href ='./directors.php'; document.getElementById('try3').submit();" >View Directors</a></li>

<li <?php if($_GET["try"] == 4) echo "class='active'" ?>>
</form>
<form id="try4" method="GET" action="index.php">
<input type="hidden" name="try" value="4" />
<a href="./movies.php" target="main" onclick="top.frames['main'].location.href ='./movies.php'; document.getElementById('try4').submit();" >View Movies</a></li>

<li <?php if($_GET["try"] == 5) echo "class='active'" ?>>
</form>
<form id="try5" method="GET" action="index.php">
<input type="hidden" name="try" value="5" />
<a href="./add.php" target="main" onclick="top.frames['main'].location.href ='./add.php'; document.getElementById('try5').submit();" >Add Content</a></li>

<li <?php if($_GET["try"] == 6) echo "class='active'" ?>>
</form>
<form id="try6" method="GET" action="index.php">
<input type="hidden" name="try" value="6" />
<a href="./comment.php" target="main" onclick="top.frames['main'].location.href ='./comment.php'; document.getElementById('try6').submit();" >Add Comment</a></li>
</form>
-->

<li><a href="./search.php" target="main">Search Results</a></li>
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

</body>
</html>

