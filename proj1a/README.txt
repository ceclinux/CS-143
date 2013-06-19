  Jonathan Nguy
603 799 761

In my project,

I used the html template from the example caculator.

Along with that, 
My PHP code does the following:

Gets the input and cleans the input by:
       replacing all spaces with blanks
       replacing -- with a +

Parses the string to match a valid expression (using preg_match)

Uses eval to calculate it, then outputs according to the evaluated result.

It should function like the test calculator except for some things:
   All spaces are removed.
   '--' results in "minus negative" (ie. plus)
