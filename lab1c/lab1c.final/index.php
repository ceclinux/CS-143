<html>
<head>
	<title>Jon Nguy's Movie Database!</title>
</head>
<FRAMESET rows="100,*, 40" FRAMEBORDER="0" BORDER="0">
	<!-- To show which tab is active 
		Not sure if this is effecient, but couldn't figure another way-->
	<?php /*if ($_GET["try"] == 1) {?>
		<FRAME NAME="index" SRC="main.php?try=1">
	<?php } elseif($_GET["try"] == 2) {?>
		<FRAME NAME="index" SRC="main.php?try=2">
	<?php } elseif($_GET["try"] == 3) {?>
		<FRAME NAME="index" SRC="main.php?try=3">
	<?php } elseif($_GET["try"] == 4) {?>
		<FRAME NAME="index" SRC="main.php?try=4">
	<?php } elseif($_GET["try"] == 5) {?>
		<FRAME NAME="index" SRC="main.php?try=5">
	<?php } elseif($_GET["try"] == 6) {?>
		<FRAME NAME="index" SRC="main.php?try=6">
	<?php } else {?><?php }*/?>
	<FRAME NAME="index" SRC="main.php">
	
	<FRAMESET cols="*,600,*" FRAMEBORDER="0" BORDER="0">
		<FRAME src="blank.html">
		<FRAME NAME="main" SRC="search.php">
		<FRAME src="blank.html">
	</FRAMESET>
	<FRAME NAME="footer" SRC="foot.html">
	
</FRAMESET>
</html>

