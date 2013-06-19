Jonathan Nguy
603 799 761

There 6 main pages of movie database website:

1. Search 			(search the whole database)
2. View Actors 		(browse all actors)
3. View Directors 	(browse all directors)
4. View Movies 		(browse all movies)
5. Add Content		(for adding Actor/Director, Movie, Relation)
6. Add Review		(comments)

Here are some things about each:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
1.	Search
---------------------------
- can search from anywhere using the top search bar
- if a string has more than 1 space, it'll make it 1 space
- entries with only spaces are INVALID
- each search word has to be at least 3 characters long
	- restriction was to avoid searches like "a", which matches almost everything
	- it allows the option to start your query with "\" to overwrite that restriction
	
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
2.	View Actors
---------------------------
- the way to browse the page is by the first letter of their names
	- once a first name is selected, the user is given the opportunity to filter it with a last name
- each actor has his/her own page 
	- the pages shows name, birthday, and death (if applicable)
- in this actor's page, we have a button where we can add him to a movie
	- this button will link to (5. Add Content) and allow the user to add the actor to a movie
- each movie that the person is in will link to the movie page

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
3.	View Directors
---------------------------
- very similar to View Actors, but shows directors
- both pages show movies they've acted in and directed 

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
4.	View Movies
---------------------------
- very similar to the other View pages
- if there's no director in a movie, you're given the option to add one
	- this links to the Add page
- you can always add an actor as well
- you can add a review
	- all these link to the "Add" page 5
	
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
5.	Add Content
---------------------------
- in this page, the user can select from a drop-down list 1 of 3 options:
	- Add Actor/Director
	- Add Movie
	- Add Actor/Director relationship with a Movie
	
---------------------------
The add Actor/ Director form:
---------------------------
- First/last text boxes (normal)
- Gender radio buttons (must be Male or Female)
- Birthday/Deathday are drop-down list
	- my code has a lot of checks to make sure date is okay
		- e.g. 29th of February. 31st of April, etc.
- only allows to add person if everything is filled in 

---------------------------
The add Movie form:
---------------------------
- Textboxes : Title/Company/Year (disabled initially)
	- These are disabled until the user selects a director
	- Year must be between 1900 and 2025
- Search textbox : director 
	- user has to pick a director before adding a movie
- Drop-down : MPAA rating
- Check boxes : Genre (as many/few as the user wants)
- User cannot add the movie until a director is picked
	- user can overwrite this by clicking a link
	- this means that the movie will not have a director

---------------------------
The add Actor/ Director relationship with a movie
---------------------------
- Has 3 search boxes that the user can search an actor/movie/director with
- User is allowed to pick either:
	1. an actor and a movie OR
	2. a director and a movie
	- any other clicks will overwrite the other
		(if there's a movie selected, selecting another movie will overwrite it)
		(if a director is selected, selecting an actor will overwrite it)
- If actor + movie is selected, user can type in a role and add
- If director + movie is selected, user can just add it

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
6.	Add Review
---------------------------
- first, the user has to select a movie (by searching)
- each search word has to be at least 3 characters long
	- restriction was to avoid searches like "a", which matches almost everything
	- it allows the option to start your query with "\" to overwrite that restriction
- once a movie is selected, the user can add a review
	- names are limited to 20 chars and the comment box is limited to 500 chars

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
** I used a CSS template from www.ramblingsoul.com, but it is all my HTML and PHP code.

Resources I used throughout:
www.tizag.com
www.w3schools.com
www.php.net
dev.mysql.com
www.richardlord.net/blog/dates-in-php-and-mysql < for mysql dates
www.stackoverflow.com 
www.kavoir.com/2009/02/php-drop-down-list.html < for drop down list
www.tizag.com/
http://www.daniweb.com/web-development/php/threads/32396/hyperlink-with-php-post
	^ sending forms through hyperlinks