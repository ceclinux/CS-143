Jonathan Nguy
603 799 761

For the query.php file, I had to figure out how to get the title of the rows, so I used the following resource:
http://php.net/manual/en/function.mysql-fetch-field.php

My own query was this:
# Titles and number of actors in Romance movies.
It makes use of 4 different tables (Actor, Movie, MovieGenre, MovieActor)

In my create.sql, I am not 100% sure the CHECK functions would work if
MySQL supported it. 

Running the command
$ mysql CS143 < load.sql
Takes a few seconds (Not sure if this is normal)

My PHP file uses 
<form method="GET">
To get the value from the box. From my tests, the outputs are fine,
but I do no input checking, so it's possible for things to go wrong.

