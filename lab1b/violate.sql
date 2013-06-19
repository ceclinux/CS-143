#Three primary key constraints:
#1	Movie : id is primary key
#2	Actor : id is primary
#3	Director : id is primary

#1
#INSERT INTO Movie VALUES (120, 'Something', 2012, 'A', 'Disney');
#ERROR 1062 (23000) at line 7: Duplicate entry '120' for key 1
#2
#INSERT INTO Actor VALUES (120, 'First', 'Last', 'Male', '2008-12-12', '2009-12-12');
#ERROR 1062 (23000) at line 9: Duplicate entry '120' for key 1
#3
#INSERT INTO Director VALUES (122, 'First', 'Last', '2008-12-12', '2009-12-12');
#ERROR 1062 (23000) at line 11: Duplicate entry '122' for key 1
# For all three of these, they violate the primary key of 'id' because there are duplicate ids.

#Six referential integrity constraints
#1	MovieGenre : mid refers to Movie id
#2	MovieDirector : mid refers to Movie id
#3					did refers to Director id
#4	MovieActor : mid refers to Movie id
#5				 aid refers to Actor id
#6	Review : mid revers to Movie id
#1
DELETE FROM Movie WHERE id >= 120 OR id <= 150;
#ERROR 1451 (23000) at line 26: Cannot delete or update a parent row: a foreign key constraint fails (`CS143/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
#2
UPDATE MovieDirector SET mid = mid + 1;
#ERROR 1452 (23000) at line 29: Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
#3
UPDATE MovieDirector SET did = did + 1;
#ERROR 1452 (23000) at line 32: Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))
#4
UPDATE MovieActor SET mid = mid + 1;
#ERROR 1452 (23000) at line 35: Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
#5
UPDATE MovieActor SET aid = aid + 1;
#ERROR 1452 (23000) at line 38: Cannot add or update a child row: a foreign key constraint fails (`CS143/MovieActor`, CONSTRAINT `MovieActor_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `Actor` (`id`))
#6
UPDATE Review SET mid = mid + 1;
#ERROR 1451 (23000) at line 42: Cannot delete or update a parent row: a foreign key constraint fails (`CS143/MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

# All of the UPDATES update their corresponding mid/aids, but they dont update Actor id or Movie id.
# All of the DELETES would delete the movie id being referenced, so that would be an error.

#Three CHECK constraints
#1	Movie id > 0 & <= MaxPersonID.id
#2	Actor id > 0 & <= MaxMovieID.id
#3	Director id > 0 <= MaxPersonID.id
#4	Review ratin >= 0 & <= 5
#1
#INSERT INTO Movie VALUES (1000000, 'Something', 2012, 'A', 'Disney');
#2
#INSERT INTO Actor VALUES (1000000, 'First', 'Last', 'Male', '2008-12-12', '2009-12-12');
#3
#INSERT INTO Director VALUES (122, 'First', 'Last', '2008-12-12', '2009-12-12');
#4
#INSERT INTO Review VALUES ('Jon', CURRENT_TIMESTAMP, 120, 100, 'This was awesome');
#These inserts go beyond what the ids or ratings should be.

############ NOTES #############
# Movie table restrictions:
# 	id must be unique
#	must have title, year, rating, company

# Actor table restrictions:
# 	id must be unique
#	must have last, first, sex, dob

# Director table restrictions:
# 	id must be unique
#	must have last, first, dob

# MovieGenre table restrictions:
# 	mid must be unique and match from movie table
#	genre must be not null

# MovieDirector table restrictions:
# 	mid must be unique and match movie table
#	did must match director id

# MovieActor table restrictions:
# 	mid must match movie table id
#	aid must match actor table id
#	role shouldnt be null

# Review table restrictions:
# 	must have name, timestamp
#	mid must match movie table id
#	must have rating

