# Movie table with primary key id
CREATE TABLE Movie (
       id INTEGER NOT NULL,
	title VARCHAR(100) NOT NULL,
	year INTEGER NOT NULL,
	rating VARCHAR(10),
	company VARCHAR(50) NOT NULL,
	PRIMARY KEY(id),
	CHECK (id > 0 AND id <= MaxMovieID(id))
) ENGINE = InnoDB;
# id has to be between 0 and the max movie ID 

# Actor table with primary key id
CREATE TABLE Actor(
	id INTEGER NOT NULL,
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	sex VARCHAR(6) NOT NULL,
	dob DATE NOT NULL,
	dod DATE DEFAULT NULL,
	PRIMARY KEY (id),
	CHECK (id > 0 AND id <= MaxPersonID(id))
) ENGINE = InnoDB;
# id has to be between 0 and the max person ID 

CREATE TABLE Director(
	id INTEGER NOT NULL,
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	dob DATE NOT NULL,
	dod DATE DEFAULT NULL,
	PRIMARY KEY (id),
	CHECK (id > 0 AND id <= MaxPersonID(id))
) ENGINE = InnoDB;
# id has to be between 0 and the max person ID 

CREATE TABLE MovieGenre(
	mid INT REFERENCES Movie(id),
	genre VARCHAR(20),
	FOREIGN KEY (mid) REFERENCES Movie(id)
) ENGINE = InnoDB;

CREATE TABLE MovieDirector(
	mid INT REFERENCES Movie(id),
	did INT REFERENCES Director(id),
	FOREIGN KEY (mid) REFERENCES Movie(id),
	FOREIGN KEY (did) REFERENCES Director(id)
) ENGINE = InnoDB;

CREATE TABLE MovieActor(
	mid INT REFERENCES Movie(id),
	aid INT REFERENCES Actor(id),
	role VARCHAR(50),
	FOREIGN KEY (mid) REFERENCES Movie(id),
	FOREIGN KEY (aid) REFERENCES Actor(id)
) ENGINE = InnoDB;

CREATE TABLE Review(
    name VARCHAR(20),
	time TIMESTAMP,
	mid INT REFERENCES Movie(id),
	rating INT,
	comment VARCHAR(500),
	FOREIGN KEY (mid) REFERENCES Movie(id),
	CHECK (rating >= 0 AND rating <= 5)
) ENGINE = InnoDB;
# id has to be between 0 and 5 "stars"

CREATE TABLE MaxPersonID(
	id INT DEFAULT 69000
);

CREATE TABLE MaxMovieID(
	id INT DEFAULT 4750
);
