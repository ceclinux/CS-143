-- Three Primary Key Constraints


-- 1. Inserting two Movies with the same id (primary key)
-- ERROR 1062 (23000) at line 6: Duplicate entry '9999' for key 1
INSERT INTO Movie VALUES (9999, 'Oceans 11', 2001, 'Excellent', 'Warner Bros.');
INSERT INTO Movie VALUES (9999, 'Oceans 12', 2004, 'Terrible', 'Warner Bros.');


-- 2. Inserting two Actors with the same id (primary key)
-- ERROR 1062 (23000) at line 3: Duplicate entry '9999' for key 1

INSERT INTO Actor VALUES (9999, 'Franco', 'James', '1982-08-08', '2100-01-01');
INSERT INfTO Actor VALUES (9999, 'Franco', 'Dave', '1989-08-08', '2109-01-01');

-- 3. Inserting two Directors with the same id (primary key)
-- ERROR 1062 (23000) at line 4: Duplicate entry '9999' for key 1
INSERT INTO Director VALUES (9999, 'Cameron', 'James', '1962-08-08', '2090-01-01');
INSERT INTO Director VALUES (9999, 'Scott', 'Ridley', '1972-08-08', '2080-01-01');



-- Six referential integrity constraints

-- 1. MovieGenre : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

INSERT INTO MovieGenre VALUES (-9999, "Action");

-- 2. MovieDirector : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
INSERT INTO MovieDirector VALUES (-9999, 123);

-- 3. MovieDirector : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
INSERT INTO MovieDirector VALUES (123, -999);

-- 4. MovieActor : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
INSERT INTO MovieActor VALUES (123, -999, "Jenny");

-- 5. MovieActor : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
INSERT INTO MovieActor VALUES (-999, 123, "Jenny");

-- 6. Review : id's cannot be less than zero
-- ERROR 1452 (23000) at line 5: Cannot add or update a child row: a foreign key constraint fails (`Test/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
INSERT INTO Review VALUES ('jsmith', '2011-03-23', -999, 10, 'Cool movie');



-- Three CHECK constraints

-- 1. Check that the Movie id is greater than zero
INSERT INTO Movie VALUES (-9999, 'Oceans 13', 2007, 'Excellent', 'Warner Bros.');

-- 2. Check that the Actor's sex is 'Male' or 'Female'
INSERT INTO Actor VALUES (123, 'Harris', 'Neil Patrick', 'Homosexual', '1982-03-05', '2080-03-21');


-- 3. Check that the Director's id is greater than zero
INSERT INTO Director VALUES (-9999, 'Cameron', 'James', '1962-08-08', '2090-01-01');


