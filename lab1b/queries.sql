#Give me the names of all the actors in the movie 'Die Another Day'.
SELECT last, first
FROM Actor A, MovieActor MA, Movie M
WHERE A.id = MA.aid AND M.id = MA.mid AND M.title = 'Die Another Day';

#Give me the count of all the actors who acted in multiple movies.
SELECT COUNT(*)
FROM (SELECT COUNT(mid)
FROM Actor A, MovieActor MA
WHERE A.id = MA.aid
GROUP BY id
HAVING COUNT(mid) > 1) A;

# My own:
# Titles and number of actors in Romance movies.
SELECT title, COUNT(DISTINCT A.id)
FROM Actor A, MovieActor MA, Movie M, MovieGenre MG
WHERE A.id = MA.aid AND M.id = MA.mid AND MG.mid = M.id AND MG.genre='Romance'
GROUP BY MA.mid
ORDER BY M.title;