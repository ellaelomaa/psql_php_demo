"""
Adding directors to personnel-table WITH surnames AND first names
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'director' as role
FROM
	temp
	LEFT OUTER JOIN people on position (people.surname in temp.director) <> 0
INTERSECT
SELECT
	temp.id as movie_id,
	people.id,
	'director' as role
FROM
	temp
	LEFT OUTER JOIN people on position (people.first_name in temp.director) <> 0 """

Updating director-column
"""
UPDATE
	temp
SET
	director = replace (temp.director, 'Directors:', '') """
Adding directors to personnel-table WITH only one name AND one director
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'director' as role
FROM
	temp
	LEFT OUTER JOIN people on position (people.surname in temp.director) <> 0
WHERE
	first_name is null
	AND position(' ' in temp.director) = 0;

"""
Adding directors to personnel-table WITH only one name who are not the first or last in the list
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'director' as role
FROM
	temp
	LEFT OUTER JOIN people on position (
		concat(', ', people.surname, ',') in temp.director
	) <> 0
WHERE
	first_name is null
	AND surname is not null;

"""
Adding directors to personnel-table WITH only one name who are the FIRST in the list
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'director' as role
FROM
	temp
	LEFT OUTER JOIN people on position (concat(people.surname, ',') in temp.director) <> 0
WHERE
	first_name is null
	AND surname is not null
	AND left(temp.director, length(people.surname)) = people.surname;

"""
Adding actors to personnel-table WITH surnames AND first names
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'actor' as role
FROM
	temp
	LEFT OUTER JOIN people on position (people.surname in temp.actors) <> 0
INTERSECT
SELECT
	temp.id as movie_id,
	people.id,
	'actor' as role
FROM
	temp
	LEFT OUTER JOIN people on position (people.first_name in temp.actors) <> 0;

"""
Adding actors to personnel-table WITH one name who are not first or last in the list
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'actor' as role
FROM
	temp
	LEFT OUTER JOIN people on position (CONCAT(', ', people.surname, ',') in temp.actors) <> 0
WHERE
	people.first_name is null
	AND people.surname is not null;

"""
Adding actors to personnel-table WITH one name who are the FIRST ones in the list
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'actor' as role
FROM
	temp
	LEFT OUTER JOIN people on position (CONCAT(people.surname, ',') in temp.actors) <> 0
WHERE
	people.first_name is null
	AND people.surname is not null
	AND left(temp.actors, length(people.surname)) = people.surname;

"""
Adding actors to personnel-table WITH one name who are the LAST ones in the list
"""
INSERT INTO
	movie_personnel
SELECT
	temp.id as movie_id,
	people.id,
	'actor' as role
FROM
	temp
	LEFT OUTER JOIN people on position (CONCAT(', ', people.surname) in temp.actors) <> 0
WHERE
	people.first_name is null
	AND people.surname is not null
	AND right(temp.actors, length(people.surname)) = people.surname;

"""
Creating movie_genres table
""" CREATE TABLE movie_genres (
	movie_id INT REFERENCES movies(id) ON DELETE CASCADE,
	genre_id INT REFERENCES genres(id) ON DELETE CASCADE,
	main BOOLEAN NOT NULL,
	PRIMARY KEY(movie_id, genre_id, main)
);

"""
Adding side genres to movie_genres-table
"""
INSERT INTO
	movie_genres
SELECT
	temp.id as movie_id,
	genres.id,
	false
FROM
	temp
	LEFT OUTER JOIN genres on position (genres.name in temp.side_genre) <> 0;

"""
Adding main genres to movie_genres-table
"""
INSERT INTO
	movie_genres
SELECT
	temp.id,
	genres.id,
	true
FROM
	temp
	LEFT OUTER JOIN genres on position (genres.name in temp.main_genre) <> 0;

"""
Adding side genres to side_genres-table
"""
INSERT INTO
	side_genres
SELECT
	temp.id as movie_id,
	genres.id
FROM
	temp
	LEFT OUTER JOIN genres on position (genres.name in temp.side_genre) <> 0;

"""
To get all personnel working on a movie.
"""
SELECT
	movies.title,
	people.surname,
	people.first_name,
	movie_personnel.role
FROM
	movies,
	movie_personnel,
	people
WHERE
	movies.id = movie_personnel.movie_id
	AND movie_personnel.person_id = people.id
ORDER BY
	title,
	role desc;

"""
Getting side genre names for movies
"""
SELECT
	*
FROM
	genres FULL
	OUTER JOIN side_genres on genres.id = side_genres.genre_id;

"""
Getting side genre names for movies (grouped to one row)
"""
SELECT
	sg.movie_id,
	string_agg(genres.name, ', ') as "Side genres"
FROM
	genres FULL
	OUTER JOIN side_genres as sg on genres.id = sg.genre_id
GROUP BY
	1;

"""
Movie info with side genre names in one row
"""
SELECT
	title,
	year,
	rating,
	runtime,
	main_genre,
	sg."Side genres"
FROM
	movies
	LEFT OUTER JOIN(
		SELECT
			sg.movie_id,
			string_agg(genres.name, ', ') as "Side genres"
		FROM
			genres FULL
			OUTER JOIN side_genres as sg on genres.id = sg.genre_id
		GROUP BY
			1
	) sg on movies.id = sg.movie_id;

"""
Movie title + directors in one row
"""
SELECT
	movies.title,
	string_agg(first_name || ' ' || surname, ', ') as "Director"
FROM
	movies,
	movie_personnel,
	people
WHERE
	movies.id = movie_personnel.movie_id
	AND movie_personnel.person_id = people.id
	AND role = 'director'
GROUP BY
	movies.id,
	movie_personnel.movie_id
ORDER BY
	movie_id;

"""
Adding director name(s) to the previous table
"""
SELECT
	title,
	year,
	rating,
	runtime,
	main_genre,
	sg."Side genres",
	string_agg(
		director.first_name || ' ' || director.surname,
		', '
	) as Director
FROM
	movies
	LEFT OUTER JOIN(
		SELECT
			sg.movie_id,
			string_agg(genres.name, ', ') as "Side genres"
		FROM
			genres FULL
			OUTER JOIN side_genres as sg on genres.id = sg.genre_id
		GROUP BY
			1
	) sg on movies.id = sg.movie_id
	LEFT OUTER JOIN(
		SELECT
			movie_id,
			surname,
			first_name
		FROM
			movie_personnel
			LEFT OUTER JOIN people on movie_personnel.person_id = people.id
		WHERE
			role = 'director'
		GROUP BY
			movie_id,
			surname
		ORDER BY
			movie_id,
			surname
	) director on movies.id = director.movie_id
GROUP BY
	movies.id,
	sg."Side genres"
ORDER BY
	movies.id;

---
"""
Adding actors' names to the previous table
"""
SELECT
	title,
	year,
	rating,
	runtime,
	mg.name as main_genre,
	sg.sidegenres as side_genres,
	director.directors as directors,
	string_agg(actors.first_name || ' ' || actors.surname, ', ') as actors
FROM
	movies
	LEFT JOIN (
		SELECT
			mg.movie_id,
			name
		FROM
			genres
			LEFT JOIN movie_genres as mg on genres.id = mg.genre_id
		WHERE
			main = true
	) mg on movies.id = mg.movie_id
	LEFT JOIN(
		SELECT
			sg.movie_id,
			string_agg(genres.name, ', ') as sidegenres
		FROM
			genres
			LEFT JOIN movie_genres as sg on genres.id = sg.genre_id
		WHERE
			main = false
		GROUP BY
			1
	) sg on movies.id = sg.movie_id
	LEFT JOIN(
		SELECT
			movie_id,
			TRIM(
				string_agg(
					COALESCE(people.first_name, '') || ' ' || people.surname,
					', '
				)
			) as directors
		FROM
			movie_personnel mp
			LEFT JOIN people on mp.person_id = people.id
		WHERE
			role = 'director'
		GROUP BY
			mp.movie_id
		ORDER BY
			mp.movie_id
	) director on movies.id = director.movie_id
	LEFT JOIN (
		SELECT
			movie_id,
			surname,
			first_name
		FROM
			movie_personnel mp
			LEFT JOIN people on mp.person_id = people.id
		WHERE
			role = 'actor'
		GROUP BY
			mp.movie_id,
			surname,
			first_name
		ORDER BY
			mp.movie_id,
			surname
	) actors on movies.id = actors.movie_id
GROUP BY
	movies.id,
	mg.name,
	sg.sidegenres,
	director.directors
ORDER BY
	movies.title
LIMIT
	10;

"""
Combining genres to one row
"""
SELECT
	movie_id,
	string_agg(genres.name, ', ') as genres
FROM
	movie_genres
	LEFT JOIN genres ON (genres.id = movie_genres.genre_id)
GROUP BY
	movie_id
ORDER BY
	movie_id;

"""
Query to find specific genres; 1-3 genres in WHERE-array
"""
LEFT JOIN(
	SELECT
		movie_id,
		string_to_array(string_agg(genres.name, ','), ',') as genres
	FROM
		movie_genres
		LEFT JOIN genres ON (genres.id = movie_genres.genre_id)
	GROUP BY
		movie_id
) all_genres on movies.id = all_genres.movie_id
WHERE
	ARRAY ['genre1', 'genre2', 'genre3'] < @ (all_genres.genres);

"""
Searching for string
"""
WHERE
	position(lower(search_word) in lower(movies.column)) > 0