<hitml>
<head><title>Calculator</title></head>
<body>

<h1>Calculator</h1>
By: Jonathan Nguy<br />
Type an expression in the following box (e.g., 10.5+20*3/25).

<p>
<form method="GET">
<input type="text" name="expr" /><input type="submit" value="Calculate" />

</form>
</p>
<ul>
<li>Only numbers and +,-,* and / operators are allowed in the expression.
<li>The evaluation follows the standard operator precedence.
<li>The calculator does not support parentheses.
<li>The calculator handles invalid input "gracefully". It does not output PHP error messages.
</ul>
Here are some(but not limit to) reasonable test cases:
<ol>
  <li> A basic arithmetic operation:  3+4*5=23 </li>
  <li> An expression with floating point or negative sign : -3.2+2*4-1/3 = 4.46666666667, 3+-2.1*2 = -1.2 </li>
  <li> Some typos inside operation (e.g. alphabetic letter): Invalid input expression 2d4+1 </li>
</ol>

<?php

  // $input = $_GET["expr"];
  // $output = "";

   //$output = $input;

   //echo ".$output";
   if($_GET["expr"]){
      $input = $_GET["expr"];

      $find = '/0';
      $cleanstr = str_replace(" ", "", $input);
      //replace -- with a +
      $cleanstr = str_replace("--", "+", $cleanstr);
      //echo "".$cleanstr."";
      preg_match("#(-?[0-9]*.?[0-9]*)( *([-+/*]) *(-?[0-9]*.?[0-9]*))*#", $cleanstr, $parsed);  
      //evaluate..
      if(strlen(strstr($cleanstr,$find))==0)
         eval("\$output = $parsed[0] ;");

      //"result" header
      echo "<html><h2>Result</h2></html><p>";

      // if the output is a number, output, if not, show the error
      if (is_numeric($output))
	 echo "".$input." = ".$output."<br/>";
      elseif (strlen(strstr($cleanstr,$find))>0) // if it finds a /0

	 echo "".$input." = <br/>";
      else
         echo "Invalid input expression ".$input."<br/>";

      }
   //echo "Hello world";

?>

</body>
</html>


