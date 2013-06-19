
-- Loads to the Movie table
LOAD DATA LOCAL INFILE './data/movie.del'  INTO TABLE Movie
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

-- Loads to the Actor table
LOAD DATA LOCAL INFILE './data/actor1.del'  INTO TABLE Actor
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

LOAD DATA LOCAL INFILE './data/actor2.del'  INTO TABLE Actor
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

LOAD DATA LOCAL INFILE './data/actor3.del'  INTO TABLE Actor
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

-- Loads to the Director table
LOAD DATA LOCAL INFILE './data/director.del'  INTO TABLE Director
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';

-- Loads to the MovieGenre table
LOAD DATA LOCAL INFILE './data/moviegenre.del'  INTO TABLE MovieGenre 
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';


-- Loads to the MovieDirector table
LOAD DATA LOCAL INFILE './data/moviedirector.del'  INTO TABLE MovieDirector
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';


-- Loads to the MovieActor table
LOAD DATA LOCAL INFILE './data/movieactor1.del'  INTO TABLE MovieActor
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';


LOAD DATA LOCAL INFILE './data/movieactor2.del'  INTO TABLE MovieActor
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"';



INSERT INTO MaxPersonID VALUES (4750);
INSERT INTO MaxMovieID VALUES (69000);